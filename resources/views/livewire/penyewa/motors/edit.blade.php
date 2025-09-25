<?php

use Livewire\Volt\Component;
use App\Models\Penyewaan;
use App\Models\Motors;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

new class extends Component {
    use Toast;

    public $booking;
    public $motor;
    public $tanggal_mulai;
    public $tanggal_selesai;
    public $tipe_durasi;
    public $harga_per_hari;
    public $total_harga;
    
    public function mount($id)
    {
        // Pastikan booking milik user dan masih bisa diedit (pending)
        $this->booking = Penyewaan::with(['motor.tarif'])
            ->where('ID_Penyewaan', $id)
            ->where('penyewa_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();
            
        $this->motor = $this->booking->motor;
        $this->tanggal_mulai = $this->booking->tanggal_mulai;
        $this->tanggal_selesai = $this->booking->tanggal_selesai;
        $this->tipe_durasi = $this->booking->tipe_durasi;
        $this->total_harga = $this->booking->harga;
        
        // Set harga per hari berdasarkan tipe durasi
        $this->setHargaPerHari();
    }

    public function setHargaPerHari()
    {
        if ($this->motor && $this->motor->tarif) {
            $this->harga_per_hari = match($this->tipe_durasi) {
                'harian' => $this->motor->tarif->tarif_harian,
                'mingguan' => $this->motor->tarif->tarif_mingguan,
                'bulanan' => $this->motor->tarif->tarif_bulanan,
                default => 0
            };
        }
        $this->hitungTotalHarga();
    }

    public function hitungTotalHarga()
    {
        if ($this->tanggal_mulai && $this->tanggal_selesai) {
            $mulai = Carbon::parse($this->tanggal_mulai);
            $selesai = Carbon::parse($this->tanggal_selesai);
            
            $durasi = match($this->tipe_durasi) {
                'harian' => $mulai->diffInDays($selesai) + 1,
                'mingguan' => $mulai->diffInWeeks($selesai) + 1,
                'bulanan' => $mulai->diffInMonths($selesai) + 1,
                default => 1
            };
            
            $this->total_harga = $this->harga_per_hari * $durasi;
        }
    }

    public function updatedTipeDurasi()
    {
        $this->setHargaPerHari();
    }

    public function updatedTanggalMulai()
    {
        $this->hitungTotalHarga();
    }

    public function updatedTanggalSelesai()
    {
        $this->hitungTotalHarga();
    }

    public function rules()
    {
        return [
            'tanggal_mulai' => 'required|date|after_or_equal:today',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'tipe_durasi' => 'required|in:harian,mingguan,bulanan',
        ];
    }

    public function messages()
    {
        return [
            'tanggal_mulai.required' => 'Tanggal mulai wajib diisi',
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai tidak boleh kurang dari hari ini',
            'tanggal_selesai.required' => 'Tanggal selesai wajib diisi',
            'tanggal_selesai.after' => 'Tanggal selesai harus setelah tanggal mulai',
            'tipe_durasi.required' => 'Tipe durasi wajib dipilih',
        ];
    }

    public function update()
    {
        $this->validate();

        try {
            $this->booking->update([
                'tanggal_mulai' => $this->tanggal_mulai,
                'tanggal_selesai' => $this->tanggal_selesai,
                'tipe_durasi' => $this->tipe_durasi,
                'harga' => $this->total_harga,
            ]);

            $this->success('Booking berhasil diupdate.');
            return redirect()->route('motors-penyewa');

        } catch (\Exception $e) {
            $this->error('Gagal mengupdate booking: ' . $e->getMessage());
        }
    }
}; ?>

<div>
    <x-header title="Edit Booking #{{ $booking->ID_Penyewaan }}" separator progress-indicator>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Kembali" link="/bookings" class="btn-outline" />
        </x-slot:actions>
    </x-header>

    <div class="p-4">
        <x-form wire:submit="update">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                {{-- Informasi Motor --}}
                <x-card title="Motor yang Disewa" class="h-fit">
                    <div class="space-y-4">
                        <x-input label="Merk" value="{{ $motor->merk }}" readonly icon="o-tag" />
                        <x-input label="Tipe/CC" value="{{ $motor->tipe_cc }}" readonly icon="o-cog-6-tooth" />
                        <x-input label="Nomor Polisi" value="{{ $motor->no_plat }}" readonly icon="o-identification" />
                        
                        <div class="bg-blue-50 p-3 rounded-lg">
                            <div class="flex items-start">
                                <x-icon name="o-information-circle" class="w-5 h-5 text-blue-500 mr-2 mt-0.5" />
                                <div class="text-sm text-blue-700">
                                    <p class="font-medium">Tarif Rental:</p>
                                    <ul class="text-xs mt-1 space-y-1">
                                        <li>• Harian: Rp {{ number_format($motor->tarif->tarif_harian ?? 0, 0, ',', '.') }}</li>
                                        <li>• Mingguan: Rp {{ number_format($motor->tarif->tarif_mingguan ?? 0, 0, ',', '.') }}</li>
                                        <li>• Bulanan: Rp {{ number_format($motor->tarif->tarif_bulanan ?? 0, 0, ',', '.') }}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-card>

                {{-- Form Edit Booking --}}
                <x-card title="Edit Detail Sewa" class="h-fit">
                    <div class="space-y-4">
                        <x-input 
                            label="Tanggal Mulai" 
                            wire:model.live="tanggal_mulai" 
                            type="date" 
                            icon="o-calendar-days"
                        />

                        <x-input 
                            label="Tanggal Selesai" 
                            wire:model.live="tanggal_selesai" 
                            type="date" 
                            icon="o-calendar-days"
                        />

                        <x-select 
                            label="Tipe Durasi" 
                            wire:model.live="tipe_durasi" 
                            :options="[
                                ['id' => 'harian', 'name' => 'Harian'],
                                ['id' => 'mingguan', 'name' => 'Mingguan'],
                                ['id' => 'bulanan', 'name' => 'Bulanan']
                            ]" 
                            option-value="id" 
                            option-label="name" 
                            placeholder="Pilih tipe durasi"
                            icon="o-clock"
                        />

                        <x-input 
                            label="Total Harga" 
                            value="Rp {{ number_format($total_harga, 0, ',', '.') }}" 
                            readonly 
                            icon="o-banknotes" 
                            class="font-bold text-green-600"
                        />

                        <div class="bg-yellow-50 p-3 rounded-lg">
                            <div class="flex items-start">
                                <x-icon name="o-exclamation-triangle" class="w-5 h-5 text-yellow-500 mr-2 mt-0.5" />
                                <div class="text-sm text-yellow-700">
                                    <p class="font-medium">Perhatian:</p>
                                    <p>Booking hanya bisa diedit selama masih berstatus "Pending". Setelah dibayar, booking tidak bisa diubah lagi.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-card>
            </div>

            <div class="flex justify-end gap-4 mt-8">
                <x-button label="Batal" link="/bookings" class="btn-outline" />
                <x-button label="Update Booking" type="submit" icon="o-check" class="btn-primary" spinner="update" />
            </div>
        </x-form>
    </div>
</div>