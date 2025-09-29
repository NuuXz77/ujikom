<?php

use Livewire\Volt\Component;
use App\Models\Motors;
use App\Models\TarifRental;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use Toast;

    public $motor;
    public $merk = '';
    public $tipe_cc = '';
    public $no_plat = '';
    public $tarif_harian = '';
    public $tarif_mingguan = '';
    public $tarif_bulanan = '';

    public array $ccOptions = ['100cc', '125cc', '150cc'];

    public function mount($id)
    {
        // Pastikan motor milik user yang sedang login
        $this->motor = Motors::with('tarif')
            ->where('ID_Motor', $id)
            ->where('owner_id', Auth::id())
            ->firstOrFail();
            
        $this->merk = $this->motor->merk;
        $this->tipe_cc = $this->motor->tipe_cc;
        $this->no_plat = $this->motor->no_plat;
        $this->tarif_harian = $this->motor->tarif->tarif_harian ?? '';
        $this->tarif_mingguan = $this->motor->tarif->tarif_mingguan ?? '';
        $this->tarif_bulanan = $this->motor->tarif->tarif_bulanan ?? '';
    }

    public function rules()
    {
        return [
            'merk' => 'required|string|max:255',
            'tipe_cc' => 'required|in:' . implode(',', $this->ccOptions),
            'no_plat' => 'required|string|max:20|unique:motors,no_plat,' . $this->motor->ID_Motor . ',ID_Motor',
            'tarif_harian' => 'required|numeric|min:0',
            'tarif_mingguan' => 'required|numeric|min:0',
            'tarif_bulanan' => 'required|numeric|min:0',
        ];
    }

    public function messages()
    {
        return [
            'merk.required' => 'Merk motor wajib diisi',
            'tipe_cc.required' => 'Tipe/CC motor wajib diisi',
            'tipe_cc.in' => 'Tipe/CC motor tidak valid',
            'no_plat.required' => 'Nomor plat wajib diisi',
            'no_plat.unique' => 'Nomor plat sudah terdaftar',
            'tarif_harian.required' => 'Tarif harian wajib diisi',
            'tarif_harian.numeric' => 'Tarif harian harus berupa angka',
            'tarif_mingguan.required' => 'Tarif mingguan wajib diisi',
            'tarif_mingguan.numeric' => 'Tarif mingguan harus berupa angka',
            'tarif_bulanan.required' => 'Tarif bulanan wajib diisi',
            'tarif_bulanan.numeric' => 'Tarif bulanan harus berupa angka',
        ];
    }

    public function update()
    {
        $this->validate();

        try {
            // Update data motor
            $this->motor->update([
                'merk' => $this->merk,
                'tipe_cc' => $this->tipe_cc,
                'no_plat' => $this->no_plat,
                'status' => 'sedang_diverifikasi' // Reset ke verifikasi karena ada perubahan
            ]);

            // Update atau create tarif
            TarifRental::updateOrCreate(
                ['motor_id' => $this->motor->ID_Motor],
                [
                    'tarif_harian' => $this->tarif_harian,
                    'tarif_mingguan' => $this->tarif_mingguan,
                    'tarif_bulanan' => $this->tarif_bulanan,
                ]
            );

            $this->success('Data motor berhasil diupdate dan akan diverifikasi ulang oleh admin.');
            return redirect()->route('motors-pemilik');

        } catch (\Exception $e) {
            $this->error('Gagal mengupdate motor: ' . $e->getMessage());
        }
    }
}; ?>

<div>
    <x-header title="Edit Motor" separator progress-indicator>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Kembali" link="/owner/motors"/>
        </x-slot:actions>
    </x-header>

    <div class="p-4">
        <x-form wire:submit="update">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                {{-- Informasi Motor --}}
                <x-card title="Informasi Motor" class="h-fit">
                    <div class="space-y-4">
                        <x-input 
                            label="Merk Motor" 
                            wire:model="merk" 
                            icon="o-tag" 
                            placeholder="Contoh: Honda, Yamaha, Suzuki"
                        />

                        <x-select 
                            label="Tipe/CC" 
                            wire:model="tipe_cc" 
                            :options="collect($ccOptions)->map(fn($cc) => ['id' => $cc, 'name' => $cc])->toArray()" 
                            option-value="id" 
                            option-label="name" 
                            placeholder="Pilih CC motor"
                            icon="o-cog-6-tooth"
                        />

                        <x-input 
                            label="Nomor Polisi" 
                            wire:model="no_plat" 
                            icon="o-identification" 
                            placeholder="Contoh: B 1234 XYZ"
                        />
                    </div>
                </x-card>

                {{-- Usulan Tarif --}}
                <x-card title="Usulan Tarif Rental" class="h-fit">
                    <div class="space-y-4">
                        <div class="bg-blue-50 p-3 rounded-lg mb-4">
                            <div class="flex items-start">
                                <x-icon name="o-information-circle" class="w-5 h-5 text-blue-500 mr-2 mt-0.5" />
                                <div class="text-sm text-blue-700">
                                    <p class="font-medium">Catatan Tarif:</p>
                                    <ul class="text-xs mt-1 space-y-1">
                                        <li>• Tarif ini adalah usulan Anda kepada admin</li>
                                        <li>• Admin dapat menyesuaikan tarif sesuai kebijakan</li>
                                        <li>• Motor akan diverifikasi ulang setelah edit</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <x-input 
                            label="Tarif Harian (Rp)" 
                            wire:model="tarif_harian" 
                            icon="o-calendar-days" 
                            placeholder="50000"
                            type="number"
                            min="0"
                        />

                        <x-input 
                            label="Tarif Mingguan (Rp)" 
                            wire:model="tarif_mingguan" 
                            icon="o-calendar" 
                            placeholder="300000"
                            type="number"
                            min="0"
                        />

                        <x-input 
                            label="Tarif Bulanan (Rp)" 
                            wire:model="tarif_bulanan" 
                            icon="o-calendar-days" 
                            placeholder="1200000"
                            type="number"
                            min="0"
                        />
                    </div>
                </x-card>
            </div>

            <div class="flex justify-end gap-4 mt-8">
                <x-button label="Batal" link="/owner/motors" />
                <x-button label="Update Motor" type="submit" icon="o-check" class="btn-primary" spinner="update" />
            </div>
        </x-form>
    </div>
</div>
