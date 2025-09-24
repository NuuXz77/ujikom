<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithPagination;

    protected $listeners = ['refresh' => 'refreshTable'];

    public int $perPage = 10;
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];
    public array $headers = [
        ['key' => 'no', 'label' => 'No', 'sortable' => false],
        ['key' => 'penyewaan', 'label' => 'Kode Sewa', 'sortable' => true],
        ['key' => 'motor', 'label' => 'Motor Saya', 'sortable' => true],
        ['key' => 'penyewa', 'label' => 'Penyewa', 'sortable' => true],
        ['key' => 'harga', 'label' => 'Harga Sewa', 'sortable' => true],
        ['key' => 'bagi_hasil_pemilik', 'label' => 'Pendapatan Saya', 'sortable' => true],
        ['key' => 'settled_at', 'label' => 'Status', 'sortable' => true],
        ['key' => 'created_at', 'label' => 'Tanggal', 'sortable' => true],
        // ['key' => 'actions', 'label' => 'Aksi', 'sortable' => false],
    ];

    public string $search = '';
    public string $status_settlement = '';// settled / pending
    public string $merk = '';
    public array $merks = [];

    public function mount(): void
    {
        $userId = Auth::id();
        // Hanya ambil merk motor milik pemilik ini
        $this->merks = \App\Models\Motors::where('owner_id', $userId)
            ->pluck('merk')->unique()->sort()->values()->toArray();
    }

    public function refreshTable(): void
    {
        $this->resetPage();
    }

    public function getRowsProperty(): LengthAwarePaginator
    {
        $userId = Auth::id();
        
        return \App\Models\BagiHasil::with(['penyewaan.motor.owner', 'penyewaan.user'])
            // Filter hanya motor milik pemilik yang login
            ->whereHas('penyewaan.motor', function ($q) use ($userId) {
                $q->where('owner_id', $userId);
            })
            ->when($this->search, function ($query) {
                $query->whereHas('penyewaan.motor', function ($q) {
                    $q->where('merk', 'like', "%{$this->search}%")
                      ->orWhere('no_plat', 'like', "%{$this->search}%");
                })->orWhereHas('penyewaan.user', function ($q) {
                    $q->where('nama', 'like', "%{$this->search}%");
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
    <x-header title="Pendapatan Saya" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input icon="o-magnifying-glass" placeholder="Cari motor / penyewa..." wire:model.live.debounce.500ms="search" />
        </x-slot:middle>
        <x-slot:actions>
            <x-dropdown label="Merk Motor">
                <x-menu-item icon="o-x-circle" title="Hapus Filter" wire:click="setMerk('')" />
                <x-menu-separator />
                @foreach ($merks as $m)
                    <x-menu-item title="{{ $m }}" wire:click="setMerk('{{ $m }}')" />
                @endforeach
            </x-dropdown>
            <x-dropdown label="Status">
                <x-menu-item icon="o-x-circle" title="Semua" wire:click="setSettlement('')" />
                <x-menu-separator />
                <x-menu-item title="Sudah Dibayar" wire:click="setSettlement('settled')" />
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
            <div class="font-medium">{{ $row->penyewaan->motor->merk ?? '-' }}</div>
            <div class="text-sm text-gray-500">{{ $row->penyewaan->motor->no_plat ?? '-' }}</div>
        @endscope
        @scope('cell_penyewa', $row)
            {{ $row->penyewaan->user->nama ?? '-' }}
        @endscope
        @scope('cell_harga', $row)
            <div class="font-medium">Rp {{ number_format($row->penyewaan->harga ?? 0, 0, ',', '.') }}</div>
        @endscope
        @scope('cell_bagi_hasil_pemilik', $row)
            <div class="font-bold text-success">Rp {{ number_format($row->bagi_hasil_pemilik, 0, ',', '.') }}</div>
            <div class="text-xs text-gray-500">30% dari total</div>
        @endscope
        @scope('cell_settled_at', $row)
            @if($row->settled_at)
                <x-badge value="Sudah Dibayar" class="badge badge-success badge-soft" />
                {{-- <span class="text-xs text-gray-500 block">
                    @if(is_string($row->settled_at))
                        {{ \Carbon\Carbon::parse($row->settled_at)->format('d/m/Y H:i') }}
                    @else
                        {{ $row->settled_at->format('d/m/Y H:i') }}
                    @endif
                </span> --}}
            @else
                <x-badge value="Pending" class="badge badge-warning badge-soft" />
                <span class="text-xs text-gray-500 block">Menunggu pembayaran</span>
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
                <x-menu-item title="Detail" icon="o-eye" link="/pemilik/revenue/detail/{{ $row->ID_Bagi_Hasil }}" />
            </x-dropdown>
        @endscope
        <x-slot:empty>
            <x-icon name="o-banknotes" label="Belum ada data pendapatan dari motor Anda." />
        </x-slot:empty>
    </x-table>
</div>
