<?php

use Livewire\Volt\Component;

new class extends Component {
    public array $headers = [['key' => 'merk', 'label' => 'Merk'], ['key' => 'tipe_cc', 'label' => 'CC'], ['key' => 'no_plat', 'label' => 'Plat'], ['key' => 'status', 'label' => 'Status'], ['key' => 'pendapatan', 'label' => 'Pendapatan'], ['key' => 'actions', 'label' => 'Aksi']];

    public string $search = '';
    public string $status = '';
    public string $merk = '';
    public string $tipe_cc = '';
    public array $merks = [];
    public array $ccOptions = ['100cc', '125cc', '150cc'];

    public function mount(): void
    {
        $userId = auth()->id();
        $this->merks = \App\Models\Motors::where('owner_id', $userId)->pluck('merk')->unique()->sort()->values()->toArray();
    }

    public function getRowsProperty()
    {
        $userId = auth()->id();
        // Ambil motor + total bagi hasil pemilik dari tabel bagi_hasils melalui penyewaans
        $motors = \App\Models\Motors::query()
            ->with(['tarif'])
            ->where('owner_id', $userId)
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('merk', 'like', "%{$this->search}%")
                      ->orWhere('no_plat', 'like', "%{$this->search}%");
                });
            })
            ->when($this->status, fn($query) => $query->where('status', $this->status))
            ->when($this->merk, fn($query) => $query->where('merk', $this->merk))
            ->when($this->tipe_cc, fn($query) => $query->where('tipe_cc', $this->tipe_cc))
            ->get();

        if ($motors->isEmpty()) {
            return collect();
        }

        // Ambil ID Motor
        $motorIds = $motors->pluck('ID_Motor');

        // Aggregate pendapatan bagi hasil (pemilik) lewat join penyewaans -> bagi_hasils
        $pendapatanMap = \DB::table('penyewaans')
            ->join('bagi_hasils', 'bagi_hasils.penyewaan_id', '=', 'penyewaans.ID_Penyewaan')
            ->select('penyewaans.motor_id', \DB::raw('SUM(bagi_hasils.bagi_hasil_pemilik) as total_pendapatan'))
            ->whereIn('penyewaans.motor_id', $motorIds)
            ->groupBy('penyewaans.motor_id')
            ->pluck('total_pendapatan', 'penyewaans.motor_id');

        return $motors->map(function($motor) use ($pendapatanMap) {
            $id = $motor->ID_Motor ?? $motor->id;
            return [
                'id' => $id,
                'merk' => $motor->merk,
                'tipe_cc' => $motor->tipe_cc,
                'no_plat' => $motor->no_plat,
                'status' => $motor->status,
                'pendapatan' => (float) ($pendapatanMap[$id] ?? 0),
            ];
        });
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function setMerk(string $merk): void
    {
        $this->merk = $merk;
    }

    public function setTipeCC(string $cc): void
    {
        $this->tipe_cc = $cc;
    }
}; ?>

<div>
    <x-header title="Daftar Motor" separator>
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" placeholder="Search..." wire:model.live.debounce.500ms="search" />
        </x-slot:middle>
        <x-slot:actions>
            <x-dropdown label="Status">
                <x-menu-item icon="o-x-circle" title="Hapus Filter" wire:click="setStatus('')" />
                <x-menu-separator />
                <x-menu-item icon="o-check-circle" title="Tersedia" wire:click="setStatus('available')" />
                <x-menu-item icon="o-shopping-cart" title="Disewa" wire:click="setStatus('rented')" />
                <x-menu-item icon="o-wrench" title="Perbaikan" wire:click="setStatus('maintenance')" />
                <x-menu-item icon="o-clock" title="Verifikasi" wire:click="setStatus('waiting_verification')" />
            </x-dropdown>

            <x-dropdown label="Merk">
                <x-menu-item icon="o-x-circle" title="Hapus Filter" wire:click="setMerk('')" />
                <x-menu-separator />
                @foreach ($merks as $merk)
                    <x-menu-item title="{{ $merk }}" wire:click="setMerk('{{ $merk }}')" />
                @endforeach
            </x-dropdown>

            <x-dropdown label="CC">
                <x-menu-item icon="o-x-circle" title="Hapus Filter" wire:click="setTipeCC('')" />
                <x-menu-separator />
                @foreach ($ccOptions as $cc)
                    <x-menu-item title="{{ $cc }}" wire:click="setTipeCC('{{ $cc }}')" />
                @endforeach
            </x-dropdown>

            <x-button icon="o-plus" class="btn-primary" link="/owner/motors/create" />
        </x-slot:actions>
    </x-header>

    <x-table :headers="$headers" :rows="$this->rows">
        @scope('cell_merk', $motor)
            {{ $motor['merk'] }}
        @endscope
        @scope('cell_tipe_cc', $motor)
            {{ $motor['tipe_cc'] }}
        @endscope
        @scope('cell_no_plat', $motor)
            {{ $motor['no_plat'] }}
        @endscope
        @scope('cell_status', $motor)
            @if ($motor['status'] == 'tersedia')
                <x-badge value="Tersedia" class="badge badge-success badge-soft" />
            @elseif ($motor['status'] == 'disewa')
                <x-badge value ="Disewa" class="badge badge-primary badge-soft" />
            @elseif ($motor['status'] == 'dibooking')
                <x-badge value="Dibooking" class="badge badge-info badge-soft" />
            @elseif ($motor['status'] == 'sedang_diverifikasi')
                <x-badge value="Sedang Diverifikasi" class="badge badge-warning badge-soft" />
            @else
                <x-badge value="Perbaikan" class="badge badge-error badge-soft" />
            @endif
        @endscope
        @scope('cell_pendapatan', $motor)
            Rp {{ number_format($motor['pendapatan'], 0, ',', '.') }}
        @endscope
        @scope('cell_actions', $motor)
            <x-dropdown>
                <x-slot:trigger>
                    <x-button icon="m-ellipsis-vertical" class="btn-circle" />
                </x-slot:trigger>
                <x-menu-item title="Detail" icon="o-eye" link="/owner/motors/detail/{{ $motor['id'] }}" />
                <x-menu-item title="Edit" icon="o-pencil" link="/owner/motors/edit/{{ $motor['id'] }}" />
                <x-menu-item title="Hapus" icon="o-trash"
                    wire:click="$dispatch('showDeleteModal', { id: '{{ $motor['id'] }}' })" class="text-red-500" />
            </x-dropdown>
        @endscope

        <x-slot:empty>
            <x-icon name="o-cube" label="Data motor tidak di temukan." />
        </x-slot:empty>
    </x-table>
</div>
