<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;

new class extends Component {
    use WithPagination;

    protected $listeners = ['refresh' => 'refreshTable'];

    public function refreshTable()
    {
        $this->resetPage();
    }
    // Properti baru untuk pagination dan sorting
    public int $perPage = 10; // Jumlah item per halaman
    public array $sortBy = ['column' => 'merk', 'direction' => 'asc']; // Default sorting

    // Update headers untuk menandai kolom yang bisa di-sort
    public array $headers = [['key' => 'no', 'label' => 'No', 'sortable' => false], ['key' => 'owner', 'label' => 'Pemilik', 'sortable' => false], ['key' => 'merk', 'label' => 'Merk', 'sortable' => true], ['key' => 'tipe_cc', 'label' => 'CC', 'sortable' => true], ['key' => 'no_plat', 'label' => 'Plat', 'sortable' => true], ['key' => 'status', 'label' => 'Status', 'sortable' => true], ['key' => 'actions', 'label' => 'Aksi', 'sortable' => false]];

    public string $search = '';
    public string $status = '';
    public string $merk = '';
    public string $tipe_cc = '';
    public array $merks = [];
    public array $ccOptions = ['100cc', '125cc', '150cc'];

    public function mount(): void
    {
        $this->merks = \App\Models\Motors::query()->pluck('merk')->unique()->sort()->values()->toArray();
    }

    // Modifikasi getRowsProperty untuk menggunakan orderBy dan paginate
    public function getRowsProperty(): LengthAwarePaginator
    {
        return \App\Models\Motors::with(['tarif',])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('merk', 'like', "%{$this->search}%")->orWhere('no_plat', 'like', "%{$this->search}%");
                });
            })
            ->when($this->status, fn($query) => $query->where('status', $this->status))
            ->when($this->merk, fn($query) => $query->where('merk', $this->merk))
            ->when($this->tipe_cc, fn($query) => $query->where('tipe_cc', $this->tipe_cc))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->resetPage();
    }

    public function setMerk(string $merk): void
    {
        $this->merk = $merk;
        $this->resetPage();
    }

    public function setTipeCC(string $cc): void
    {
        $this->tipe_cc = $cc;
        $this->resetPage();
    }
}; ?>

<div>
    <x-header title="Daftar Motor" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input icon="o-bolt" placeholder="Search..." wire:model.live.debounce.500ms="search" />
        </x-slot:middle>
        <x-slot:actions>
            {{-- Bagian dropdown filter tidak berubah --}}
            <x-dropdown label="Status">
                <x-menu-item icon="o-x-circle" title="Hapus Filter" wire:click="setStatus('')" />
                <x-menu-separator />
                <x-menu-item icon="o-check-circle" title="Tersedia" wire:click="setStatus('tersedia')" />
                <x-menu-item icon="o-shopping-cart" title="Disewa" wire:click="setStatus('disewa')" />
                <x-menu-item icon="o-wrench" title="Perawatan" wire:click="setStatus('perawatan')" />
                <x-menu-item icon="o-clock" title="Verifikasi" wire:click="setStatus('sedang_diverifikasi')" />
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

            <x-button icon="o-plus" class="btn-primary" link="/admin/motors/create" />
        </x-slot:actions>
    </x-header>

    {{-- Update x-table dengan atribut sorting dan pagination --}}
    <x-table :headers="$headers" :rows="$this->rows" :sort-by="$sortBy" :per-page="$perPage" :per-page-values="[5, 10, 20, 50]" with-pagination>
        {{-- Ubah scope untuk menggunakan properti objek, bukan array --}}
        @scope('cell_no', $motor, $loop)
            {{ ($this->rows->currentPage() - 1) * $this->rows->perPage() + $loop->iteration }}
        @endscope
        @scope('cell_owner', $motor)
            {{ $motor->owner->nama ?? '-' }}
        @endscope
        @scope('cell_merk', $motor)
            {{ $motor->merk }}
        @endscope
        @scope('cell_tipe_cc', $motor)
            {{ $motor->tipe_cc }}
        @endscope
        @scope('cell_no_plat', $motor)
            {{ $motor->no_plat }}
        @endscope
        @scope('cell_status', $motor)
            @if ($motor['status'] == 'tersedia')
                <x-badge value="Tersedia" class="badge badge-success badge-soft" />
            @elseif ($motor->status == 'disewa')
                <x-badge value="Disewa" class="badge badge-primary badge-soft" />
            @elseif ($motor->status == 'sedang_diverifikasi')
                <x-badge value="Perlu Diverifikasi" class="badge badge-warning badge-soft" />
            @elseif ($motor->status == 'dibooking')
                <x-badge value="Dibooking" class="badge badge-info badge-soft" />
            @elseif ($motor->status == 'dibayar')
                <x-badge value="Dibayar" class="badge badge-info badge-soft" />
            @elseif ($motor->status == 'dikembalikan')
                <x-badge value="Dikembalikan" class="badge badge-error badge-soft" />
            @else
                <x-badge value="Perawatan" class="badge badge-error badge-soft" />
            @endif
        @endscope
        @scope('cell_actions', $motor)
            <x-dropdown>
                <x-slot:trigger>
                    <x-button icon="m-ellipsis-vertical" class="btn-circle" />
                </x-slot:trigger>
                <x-menu-item title="Detail" icon="o-eye" link="/admin/motors/detail/{{ $motor->ID_Motor }}" />
                <x-menu-item title="Edit" icon="o-pencil" link="/admin/motors/edit/{{ $motor->ID_Motor }}" />
                <x-menu-item title="Hapus" icon="o-trash"
                    wire:click="$dispatch('showDeleteModal', { id: {{ $motor->ID_Motor }} })" class="text-red-500" />
            </x-dropdown>
        @endscope

        <x-slot:empty>
            <x-icon name="o-cube" label="Data motor tidak di temukan." />
        </x-slot:empty>
    </x-table>

    <livewire:admin.motors.delete />
</div>