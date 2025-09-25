<?php

use Livewire\Volt\Component;
use App\Models\Penyewaan;
use App\Models\Motors;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

new class extends Component {
    use Toast;

    public bool $returnModal = false;
    public $bookingId, $kode, $motor, $plat, $mulai, $selesai, $durasi, $harga;
    public string $catatan = '';
    public $listeners = ['showReturnModal' => 'openModal'];

    public function openModal($id)
    {
        $id = is_array($id) && isset($id['id']) ? $id['id'] : $id;
        $booking = Penyewaan::with('motor')
            ->where('ID_Penyewaan', $id)
            ->where('penyewa_id', Auth::id())
            ->whereIn('status', ['dibayar', 'disewa'])
            ->firstOrFail();

        $this->bookingId = $booking->ID_Penyewaan;
        $this->kode = '#'.$booking->ID_Penyewaan;
        $this->motor = $booking->motor?->merk;
        $this->plat = $booking->motor?->no_plat;
        $this->mulai = $booking->tanggal_mulai;
        $this->selesai = $booking->tanggal_selesai;
        $this->durasi = ucfirst($booking->tipe_durasi);
        $this->harga = $booking->harga;
        $this->catatan = '';
        $this->returnModal = true;
    }

    public function returnMotor()
    {
        try {
            DB::beginTransaction();

            $booking = Penyewaan::with('motor')
                ->where('ID_Penyewaan', $this->bookingId)
                ->where('penyewa_id', Auth::id())
                ->whereIn('status', ['dibayar', 'disewa'])
                ->first();

            if (!$booking) {
                throw new \Exception('Booking tidak ditemukan atau tidak dapat dikembalikan.');
            }

            // Update status booking menjadi "dikembalikan"
            $booking->update([
                'status' => 'menunggu_verifikasi_pengembalian',
            ]);
            
            // Update status motor menjadi tersedia
            if ($booking->motor) {
                $booking->motor->update(['status' => 'dikembalikan']);
            }

            DB::commit();

            $this->success('Motor berhasil dikembalikan.');
            $this->dispatch('refresh');
            
            // Reset dan tutup modal
            $this->reset(['bookingId','kode','motor','plat','mulai','selesai','durasi','harga','catatan']);
            $this->returnModal = false;

        } catch (\Exception $e) {
            DB::rollback();
            $this->error('Gagal mengembalikan motor: ' . $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->reset(['bookingId','kode','motor','plat','mulai','selesai','durasi','harga','catatan']);
        $this->returnModal = false;
        $this->resetErrorBag();
    }
}; ?>

<div>
    <x-modal wire:model="returnModal" title="Kembalikan Motor" persistent class="backdrop-blur">
        <div class="mb-4 space-y-4">
            <div class="text-sm">
                <p class="mb-3">Konfirmasi pengembalian motor:</p>
                <div class="bg-base-200 p-4 rounded-lg space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-600">Kode Booking:</span>
                        <strong>{{ $kode }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Motor:</span>
                        <strong>{{ $motor }} ({{ $plat }})</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Periode:</span>
                        <strong>{{ $mulai }} s/d {{ $selesai }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Durasi:</span>
                        <strong>{{ $durasi }}</strong>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-600">Total Bayar:</span>
                        <strong class="text-green-600">Rp {{ number_format($harga ?? 0,0,',','.') }}</strong>
                    </div>
                </div>
            </div>

            <div class="bg-blue-50 p-3 rounded-lg">
                <div class="flex items-start">
                    <x-icon name="o-information-circle" class="w-5 h-5 text-blue-500 mr-2 mt-0.5" />
                    <div class="text-sm text-blue-700">
                        <p class="font-medium mb-1">Informasi Pengembalian:</p>
                        <ul class="text-xs space-y-1">
                            <li>• Motor akan dikembalikan dengan status "Selesai"</li>
                            <li>• Status motor akan berubah menjadi "Tersedia"</li>
                            <li>• Tidak ada pemotongan biaya untuk pengembalian lebih awal</li>
                            <li>• Catatan pengembalian akan disimpan untuk referensi</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Batal" wire:click="closeModal" />
            <x-button 
                label="Kembalikan Motor" 
                wire:click="returnMotor" 
                class="btn-primary" 
                spinner="returnMotor" 
            />
        </x-slot:actions>
    </x-modal>
</div>
