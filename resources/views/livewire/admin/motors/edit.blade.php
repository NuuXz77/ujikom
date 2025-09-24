<?php

use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Livewire\WithFileUploads;

new class extends Component {
    use Toast, WithFileUploads;

    public $motor;
    public $owner;
    
    // Motor information
    public string $merk = '';
    public string $tipe_cc = '';
    public string $no_plat = '';
    public string $status = '';
    public $photo;
    public $dokumen_kepemilikan;
    public $new_photo;
    public $new_dokumen;
    
    // Tarif information
    public $tarif_harian = 0;
    public $tarif_mingguan = 0;
    public $tarif_bulanan = 0;

    public $tarif_harian_hint = '';
    public $tarif_mingguan_hint = '';
    public $tarif_bulanan_hint = '';

    public $owner_tarif_harian = 0;
    public $owner_tarif_mingguan = 0;
    public $owner_tarif_bulanan = 0;

    // Options
    public array $statusOptions = [
        ['id' => 'tersedia', 'name' => 'Tersedia'],
        ['id' => 'disewa', 'name' => 'Disewa'],
        ['id' => 'perawatan', 'name' => 'Perawatan'],
        ['id' => 'sedang_diverifikasi', 'name' => 'Sedang Diverifikasi'],
        ['id' => 'dibooking', 'name' => 'Dibooking']
    ];

    public array $ccOptions = [
        ['id' => '100cc', 'name' => '100cc'],
        ['id' => '125cc', 'name' => '125cc'],
        ['id' => '150cc', 'name' => '150cc'],
        ['id' => '200cc', 'name' => '200cc'],
        ['id' => '250cc', 'name' => '250cc'],
    ];

    protected $rules = [
        'merk' => 'required|string|max:255',
        'tipe_cc' => 'required|string',
        'no_plat' => 'required|string|max:20',
        'status' => 'required|string',
        'tarif_harian' => 'required|numeric|min:0',
        'tarif_mingguan' => 'required|numeric|min:0',
        'tarif_bulanan' => 'required|numeric|min:0',
        'new_photo' => 'nullable|image|max:2048',
        'new_dokumen' => 'nullable|image|max:2048',
    ];

    public function mount($id)
    {
        $this->motor = \App\Models\Motors::with(['tarif', 'owner'])->findOrFail($id);
        $this->owner = $this->motor->owner;
        
        // Load motor data
        $this->merk = $this->motor->merk;
        $this->tipe_cc = $this->motor->tipe_cc;
        $this->no_plat = $this->motor->no_plat;
        $this->status = $this->motor->status;
        $this->photo = $this->motor->photo;
        $this->dokumen_kepemilikan = $this->motor->dokumen_kepemilikan;
        
        // Load tarif data
        $this->tarif_harian = $this->motor->tarif->tarif_harian ?? 0;
        $this->tarif_mingguan = $this->motor->tarif->tarif_mingguan ?? 0;
        $this->tarif_bulanan = $this->motor->tarif->tarif_bulanan ?? 0;

        // Simpan harga usulan pemilik (sementara gunakan tarif yang ada)
        $this->owner_tarif_harian = $this->motor->tarif->tarif_harian ?? 0;
        $this->owner_tarif_mingguan = $this->motor->tarif->tarif_mingguan ?? 0;
        $this->owner_tarif_bulanan = $this->motor->tarif->tarif_bulanan ?? 0;

        $this->updateHints();
    }

    public function updatedTarifHarian()
    {
        $this->updateHints();
    }

    public function updatedTarifMingguan()
    {
        $this->updateHints();
    }

    public function updatedTarifBulanan()
    {
        $this->updateHints();
    }

    public function updateHints()
    {
        $this->tarif_harian_hint = $this->getRateHint($this->owner_tarif_harian, $this->tarif_harian);
        $this->tarif_mingguan_hint = $this->getRateHint($this->owner_tarif_mingguan, $this->tarif_mingguan);
        $this->tarif_bulanan_hint = $this->getRateHint($this->owner_tarif_bulanan, $this->tarif_bulanan);
    }

    public function getRateHint($ownerRate, $adminRate)
    {
        $ownerRate = (float) $ownerRate;
        $adminRate = is_numeric($adminRate) ? (float) $adminRate : null;

        if ($adminRate === null) {
            return 'Usulan pemilik: Rp ' . number_format($ownerRate, 0, ',', '.');
        }

        if ($ownerRate <= 0) {
            return 'Tidak ada harga usulan dari pemilik.';
        }

        if ($adminRate == $ownerRate) {
            return 'Harga sama, tidak ada keuntungan.';
        }

        $selisih = $adminRate - $ownerRate;
        $persen = round(($selisih / $ownerRate) * 100, 2);
        $formattedSelisih = 'Rp ' . number_format(abs($selisih), 0, ',', '.');

        if ($selisih > 0) {
            return "Keuntungan: +{$formattedSelisih} ({$persen}%)";
        } else {
            return "Lebih rendah: -{$formattedSelisih} ({$persen}%)";
        }
    }

    public function update()
    {
        $this->validate();

        try {
            // Update motor data
            $this->motor->merk = $this->merk;
            $this->motor->tipe_cc = $this->tipe_cc;
            $this->motor->no_plat = $this->no_plat;
            $this->motor->status = $this->status;

            // Handle photo upload
            if ($this->new_photo) {
                // Delete old photo if exists
                if ($this->photo) {
                    \Storage::delete('public/' . $this->photo);
                }
                $this->motor->photo = $this->new_photo->store('motors', 'public');
            }

            // Handle dokumen upload
            if ($this->new_dokumen) {
                // Delete old dokumen if exists
                if ($this->dokumen_kepemilikan) {
                    \Storage::delete('public/' . $this->dokumen_kepemilikan);
                }
                $this->motor->dokumen_kepemilikan = $this->new_dokumen->store('dokumen', 'public');
            }

            $this->motor->save();

            // Update or create tarif
            $tarif = $this->motor->tarif ?? new \App\Models\TarifRental();
            $tarif->motor_id = $this->motor->ID_Motor;
            $tarif->tarif_harian = $this->tarif_harian;
            $tarif->tarif_mingguan = $this->tarif_mingguan;
            $tarif->tarif_bulanan = $this->tarif_bulanan;
            $tarif->save();

            $this->toast('success', 'Sukses!', 'Data motor berhasil diperbarui.');
            return redirect('/admin/motors');

        } catch (\Exception $e) {
            $this->toast('error', 'Error!', 'Gagal memperbarui data motor: ' . $e->getMessage());
        }
    }
}; ?>

<div>
    <x-header title="Edit Motor {{ $motor->merk }}" separator progress-indicator>
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <x-button icon="o-arrow-left" label="" link="/admin/motors" class="btn-ghost" />
                @if ($motor->status == 'sedang_diverifikasi')
                    <x-badge value="Sedang Diverifikasi" class="badge badge-warning badge-soft" />
                @elseif ($motor->status == 'tersedia')
                    <x-badge value="Tersedia" class="badge badge-success badge-soft" />
                @elseif ($motor->status == 'disewa')
                    <x-badge value="Disewa" class="badge badge-primary badge-soft" />
                @elseif ($motor->status == 'perawatan')
                    <x-badge value="Perawatan" class="badge badge-error badge-soft" />
                @else
                    <x-badge value="-" class="badge badge-soft" />
                @endif
            </div>
        </x-slot:actions>
    </x-header>

    <div class="p-4">
        <x-form wire:submit.prevent="update">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                
                {{-- Informasi Motor (Editable) --}}
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <x-icon name="o-truck" class="w-5 h-5" />
                        Informasi Motor
                    </h2>

                    <x-input label="Merk" wire:model="merk" required icon="o-tag" />
                    
                    <x-select label="CC" :options="$ccOptions" option-label="name" option-value="id" 
                        wire:model="tipe_cc" required icon="o-cog-6-tooth" />
                    
                    <x-input label="Nomor Polisi" wire:model="no_plat" required icon="o-identification" />
                    
                    <x-select label="Status" :options="$statusOptions" option-label="name" option-value="id" 
                        wire:model="status" required icon="o-signal" />

                    {{-- Photo Upload --}}
                    <div>
                        <label class="block text-sm font-medium mb-1 flex items-center gap-2">
                            <x-icon name="o-camera" class="w-4 h-4" />
                            Foto Motor
                        </label>
                        @if ($photo && !$new_photo)
                            <img src="{{ asset('storage/' . $photo) }}" alt="Foto Motor"
                                class="h-40 w-full object-cover rounded-lg mb-2"
                                onerror="this.src='https://placehold.co/400x200?text=Foto+Tidak+Valid';" />
                        @endif
                        @if ($new_photo)
                            <img src="{{ $new_photo->temporaryUrl() }}" alt="Preview Foto"
                                class="h-40 w-full object-cover rounded-lg mb-2" />
                        @endif
                        <x-file wire:model="new_photo" accept="image/*" hint="Upload foto baru (opsional)" />
                    </div>

                    {{-- Dokumen Upload --}}
                    <div>
                        <label class="block text-sm font-medium mb-1 flex items-center gap-2">
                            <x-icon name="o-document-text" class="w-4 h-4" />
                            Dokumen Kepemilikan
                        </label>
                        @if ($dokumen_kepemilikan && !$new_dokumen)
                            <img src="{{ asset('storage/' . $dokumen_kepemilikan) }}" alt="Dokumen Kepemilikan"
                                class="h-40 w-full object-cover rounded-lg mb-2"
                                onerror="this.src='https://placehold.co/400x200?text=Dokumen+Tidak+Valid';" />
                        @endif
                        @if ($new_dokumen)
                            <img src="{{ $new_dokumen->temporaryUrl() }}" alt="Preview Dokumen"
                                class="h-40 w-full object-cover rounded-lg mb-2" />
                        @endif
                        <x-file wire:model="new_dokumen" accept="image/*" hint="Upload dokumen baru (opsional)" />
                    </div>
                </div>

                {{-- Informasi Pemilik (Read Only) & Tarif (Editable) --}}
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <x-icon name="o-user" class="w-5 h-5" />
                        Informasi Pemilik
                    </h2>

                    <x-input label="Nama Pemilik" value="{{ $owner->nama ?? '-' }}" readonly icon="o-user-circle" />
                    <x-input label="Email Pemilik" value="{{ $owner->email ?? '-' }}" readonly icon="o-envelope" />
                    <x-input label="No. HP Pemilik" value="{{ $owner->no_telp ?? '-' }}" readonly icon="o-phone" />

                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <x-icon name="o-currency-dollar" class="w-5 h-5" />
                        Tarif Sewa
                    </h2>

                    <x-input label="Tarif Harian" type="number" wire:model.live="tarif_harian" min="0"
                        hint="{{ $tarif_harian_hint }}" required icon="o-sun" />
                    
                    <x-input label="Tarif Mingguan" type="number" wire:model.live="tarif_mingguan" min="0"
                        hint="{{ $tarif_mingguan_hint }}" required icon="o-calendar-days" />
                    
                    <x-input label="Tarif Bulanan" type="number" wire:model.live="tarif_bulanan" min="0"
                        hint="{{ $tarif_bulanan_hint }}" required icon="o-calendar" />

                    <div class="mt-6 flex gap-2">
                        <x-button type="submit" icon="o-check" class="btn-primary">Update Motor</x-button>
                        <x-button type="button" icon="o-x-mark" class="btn-ghost" link="/admin/motors">Batal</x-button>
                    </div>
                </div>
            </div>
        </x-form>
    </div>
</div>
