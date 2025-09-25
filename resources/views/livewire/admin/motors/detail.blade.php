<?php

use Livewire\Volt\Component;

new class extends Component {
    public $motor;
    public $owner;
    public $tarif_harian;
    public $tarif_mingguan;
    public $tarif_bulanan;

    public $tarif_harian_hint = '';
    public $tarif_mingguan_hint = '';
    public $tarif_bulanan_hint = '';

    public $owner_tarif_harian = 0;
    public $owner_tarif_mingguan = 0;
    public $owner_tarif_bulanan = 0;

    public function mount($id)
    {
        $this->motor = \App\Models\Motors::with(['tarif', 'owner'])->findOrFail($id);
        $this->owner = $this->motor->owner;
        $this->tarif_harian = $this->motor->tarif->tarif_harian ?? '';
        $this->tarif_mingguan = $this->motor->tarif->tarif_mingguan ?? '';
        $this->tarif_bulanan = $this->motor->tarif->tarif_bulanan ?? '';

        // Simpan harga usulan pemilik (sementara gunakan tarif yang ada)
        $this->owner_tarif_harian = $this->motor->tarif->tarif_harian ?? 0;
        $this->owner_tarif_mingguan = $this->motor->tarif->tarif_mingguan ?? 0;
        $this->owner_tarif_bulanan = $this->motor->tarif->tarif_bulanan ?? 0;

        $this->updateHints();
    }

    // Gunakan updated hook yang lebih efisien dari Livewire 3
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
        // Ubah input menjadi numerik untuk perbandingan yang aman
        $ownerRate = (float) $ownerRate;
        // Jika input admin kosong atau bukan angka, anggap null
        $adminRate = is_numeric($adminRate) ? (float) $adminRate : null;

        // Jika admin belum mengetik apa pun, tampilkan harga usulan pemilik
        if ($adminRate === null) {
            return 'Usulan pemilik: Rp ' . number_format($ownerRate, 0, ',', '.');
        }

        // Jika harga usulan pemilik 0, tidak ada dasar perbandingan
        if ($ownerRate <= 0) {
            return 'Tidak ada harga usulan dari pemilik.';
        }

        // Jika harga sama
        if ($adminRate == $ownerRate) {
            return 'Harga sama, tidak ada keuntungan.';
        }

        // Hitung selisih dan persentase
        $selisih = $adminRate - $ownerRate;
        $persen = round(($selisih / $ownerRate) * 100, 2);

        // Format angka selisih
        $formattedSelisih = 'Rp ' . number_format(abs($selisih), 0, ',', '.');

        if ($selisih > 0) {
            // Admin menetapkan harga lebih tinggi (keuntungan)
            return "Keuntungan: +{$formattedSelisih} ({$persen}%)";
        } else {
            // Admin menetapkan harga lebih rendah
            return "Lebih rendah: -{$formattedSelisih} ({$persen}%)";
        }
    }

    public function verify()
    {
        // Set status menjadi 'tersedia'
        $this->motor->status = 'tersedia';
        $this->motor->save();

        $tarif = $this->motor->tarif ?? new \App\Models\TarifRental();
        $tarif->motor_id = $this->motor->ID_Motor;
        $tarif->tarif_harian = $this->tarif_harian ?: 0;
        $tarif->tarif_mingguan = $this->tarif_mingguan ?: 0;
        $tarif->tarif_bulanan = $this->tarif_bulanan ?: 0;
        $tarif->save();

        session()->flash('success', 'Motor berhasil diverifikasi dan tarif disimpan.');
        return redirect('/admin/motors');
    }

    public function reject()
    {
        $this->motor->status = 'perawatan'; // Atau bisa dibuat status khusus seperti 'ditolak'
        $this->motor->save();
        session()->flash('error', 'Motor ditandai untuk perbaikan / ditolak.');
        return redirect('/admin/motors');
    }
}; ?>

<div>
    <x-header title="Detail Motor {{ $motor->merk }}" separator progress-indicator>
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
                @elseif ($motor->status == 'dibayar')
                    <x-badge value="Dibayar" class="badge badge-info badge-soft" />
                @else
                    <x-badge value="-" class="badge badge-soft" />
                @endif
            </div>
        </x-slot:actions>
    </x-header>

    <div class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
            
            {{-- Informasi Motor --}}
            <div class="space-y-4">
                <h2 class="text-lg font-semibold flex items-center gap-2">
                    <x-icon name="o-truck" class="w-5 h-5" />
                    Informasi Motor
                </h2>

                <x-input label="Merk" value="{{ $motor->merk }}" readonly icon="o-tag" />
                <x-input label="CC" value="{{ $motor->tipe_cc }}" readonly icon="o-cog-6-tooth" />
                <x-input label="Nomor Polisi" value="{{ $motor->no_plat }}" readonly icon="o-identification" />
                <x-input label="Status" value="{{ ucfirst($motor->status) }}" readonly icon="o-signal" />

                {{-- Photo --}}
                <div>
                    <label class="block text-sm font-medium mb-1 flex items-center gap-2">
                        <x-icon name="o-camera" class="w-4 h-4" />
                        Foto Motor
                    </label>
                    <img src="{{ asset('storage/' . $motor->photo) }}" alt="Foto Motor"
                        class="h-60 w-full object-cover rounded-lg"
                        onerror="this.src='https://placehold.co/400x300?text=Foto+Tidak+Tersedia';" />
                </div>

                {{-- Dokumen --}}
                <div>
                    <label class="block text-sm font-medium mb-1 flex items-center gap-2">
                        <x-icon name="o-document-text" class="w-4 h-4" />
                        Dokumen Kepemilikan
                    </label>
                    <img src="{{ asset('storage/' . $motor->dokumen_kepemilikan) }}" alt="Dokumen Kepemilikan"
                        class="h-60 w-full object-cover rounded-lg"
                        onerror="this.src='https://placehold.co/400x300?text=Dokumen+Tidak+Tersedia';" />
                </div>
            </div>

            {{-- Informasi Pemilik & Tarif --}}
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
                    hint="{{ $tarif_harian_hint }}" icon="o-sun" />
                
                <x-input label="Tarif Mingguan" type="number" wire:model.live="tarif_mingguan" min="0"
                    hint="{{ $tarif_mingguan_hint }}" icon="o-calendar-days" />
                
                <x-input label="Tarif Bulanan" type="number" wire:model.live="tarif_bulanan" min="0"
                    hint="{{ $tarif_bulanan_hint }}" icon="o-calendar" />

                @if ($motor->status == 'sedang_diverifikasi')
                    <div class="mt-6 flex gap-2">
                        <x-button wire:click="verify" icon="o-check-circle" class="btn-success">
                            Verifikasi & Setujui
                        </x-button>
                        <x-button wire:click="reject" icon="o-x-circle" class="btn-error">
                            Tolak
                        </x-button>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
