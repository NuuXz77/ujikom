<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;

new class extends Component {
    use WithPagination;

    protected $listeners = ['refresh' => 'refreshTable'];

    public int $perPage = 10;
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];
    public array $headers = [
        ['key' => 'no', 'label' => 'No', 'sortable' => false],
        ['key' => 'penyewaan', 'label' => 'Kode Sewa', 'sortable' => true],
        ['key' => 'motor', 'label' => 'Motor', 'sortable' => true],
        ['key' => 'pemilik', 'label' => 'Pemilik', 'sortable' => true],
        ['key' => 'harga', 'label' => 'Harga Sewa', 'sortable' => true],
        ['key' => 'bagi_hasil_pemilik', 'label' => 'Pemilik', 'sortable' => true],
        ['key' => 'bagi_hasil_admin', 'label' => 'Admin', 'sortable' => true],
        ['key' => 'settled_at', 'label' => 'Settled', 'sortable' => true],
        ['key' => 'created_at', 'label' => 'Dibuat', 'sortable' => true],
        ['key' => 'actions', 'label' => 'Aksi', 'sortable' => false],
    ];

    public string $search = '';
    public string $status_settlement = '';// settled / pending
    public string $merk = '';
    public array $merks = [];

    public function mount(): void
    {
        $this->merks = \App\Models\Motors::query()->pluck('merk')->unique()->sort()->values()->toArray();
    }

    public function refreshTable(): void
    {
        $this->resetPage();
    }

    public function getRowsProperty(): LengthAwarePaginator
    {
        return \App\Models\BagiHasil::with(['penyewaan.motor.owner'])
            ->when($this->search, function ($query) {
                $query->whereHas('penyewaan.motor', function ($q) {
                    $q->where('merk', 'like', "%{$this->search}%")
                      ->orWhere('no_plat', 'like', "%{$this->search}%");
                });
            })
            ->when($this->merk, fn($q) => $q->whereHas('penyewaan.motor', fn($m) => $m->where('merk', $this->merk)))
            ->when($this->status_settlement, function($q){
                if ($this->status_settlement === 'settled') {
                    $q->whereNotNull('settled_at');
                } elseif ($this->status_settlement === 'pending') {
                    $q->whereNull('settled_at');
                }
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function setMerk(string $merk): void
    {
        $this->merk = $merk; $this->resetPage();
    }

    public function setSettlement(string $status): void
    {
        $this->status_settlement = $status; $this->resetPage();
    }
}; ?>

<div>
    <x-header title="Bagi Hasil" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input icon="o-magnifying-glass" placeholder="Cari motor / plat..." wire:model.live.debounce.500ms="search" />
        </x-slot:middle>
        <x-slot:actions>
            <x-dropdown label="Merk">
                <x-menu-item icon="o-x-circle" title="Hapus Filter" wire:click="setMerk('')" />
                <x-menu-separator />
                @foreach ($merks as $m)
                    <x-menu-item title="{{ $m }}" wire:click="setMerk('{{ $m }}')" />
                @endforeach
            </x-dropdown>
            <x-dropdown label="Settlement">
                <x-menu-item icon="o-x-circle" title="Semua" wire:click="setSettlement('')" />
                <x-menu-separator />
                <x-menu-item title="Settled" wire:click="setSettlement('settled')" />
                <x-menu-item title="Pending" wire:click="setSettlement('pending')" />
            </x-dropdown>
        </x-slot:actions>
    </x-header>

    <x-table :headers="$headers" :rows="$this->rows" :sort-by="$sortBy" :per-page="$perPage" :per-page-values="[5,10,20,50]" with-pagination>
        @scope('cell_no', $row, $loop)
            {{ ($this->rows->currentPage() - 1) * $this->rows->perPage() + $loop->iteration }}
        @endscope
        @scope('cell_penyewaan', $row)
            #{{ $row->penyewaan->ID_Penyewaan ?? '-' }}
        @endscope
        @scope('cell_motor', $row)
            {{ $row->penyewaan->motor->merk ?? '-' }} ({{ $row->penyewaan->motor->no_plat ?? '-' }})
        @endscope
        @scope('cell_pemilik', $row)
            {{ $row->penyewaan->motor->owner->nama ?? '-' }}
        @endscope
        @scope('cell_harga', $row)
            Rp {{ number_format($row->penyewaan->harga ?? 0, 0, ',', '.') }}
        @endscope
        @scope('cell_bagi_hasil_pemilik', $row)
            Rp {{ number_format($row->bagi_hasil_pemilik, 0, ',', '.') }}
        @endscope
        @scope('cell_bagi_hasil_admin', $row)
            Rp {{ number_format($row->bagi_hasil_admin, 0, ',', '.') }}
        @endscope
        @scope('cell_settled_at', $row)
            @if($row->settled_at)
                <x-badge value="Settled" class="badge badge-success badge-soft" />
                <span class="text-xs text-gray-500 block">
                    @if(is_string($row->settled_at))
                        {{ \Carbon\Carbon::parse($row->settled_at)->format('d/m/Y H:i') }}
                    @else
                        {{ $row->settled_at->format('d/m/Y H:i') }}
                    @endif
                </span>
            @else
                <x-badge value="Pending" class="badge badge-warning badge-soft" />
            @endif
        @endscope
        @scope('cell_created_at', $row)
            {{ $row->created_at->format('d/m/Y') }}
        @endscope
        @scope('cell_actions', $row)
            <x-dropdown>
                <x-slot:trigger>
                    <x-button icon="m-ellipsis-vertical" class="btn-circle" />
                </x-slot:trigger>
                <x-menu-item title="Detail" icon="o-eye" link="/admin/revenue/detail/{{ $row->ID_Bagi_Hasil }}" />
            </x-dropdown>
        @endscope
        <x-slot:empty>
            <x-icon name="o-banknotes" label="Data bagi hasil tidak ditemukan." />
        </x-slot:empty>
    </x-table>
</div>
