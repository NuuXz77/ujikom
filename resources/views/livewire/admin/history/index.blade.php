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
        ['key' => 'kode', 'label' => 'Kode Sewa', 'sortable' => true],
        ['key' => 'tanggal', 'label' => 'Tanggal', 'sortable' => true],
        ['key' => 'motor', 'label' => 'Motor', 'sortable' => true],
        ['key' => 'penyewa', 'label' => 'Penyewa', 'sortable' => true],
        ['key' => 'metode_pembayaran', 'label' => 'Metode', 'sortable' => true],
        ['key' => 'status', 'label' => 'Status', 'sortable' => true],
        ['key' => 'harga', 'label' => 'Harga', 'sortable' => true],
        ['key' => 'actions', 'label' => 'Aksi', 'sortable' => false],
    ];

    public string $search = '';
    public string $statusTransaksi = '';
    public string $metode = '';
    public string $merk = '';
    public array $metodeOptions = [];
    public array $merks = [];

    public function mount(): void
    {
        $this->metodeOptions = \App\Models\Pembayaran::query()->pluck('metode_pembayaran')->unique()->sort()->values()->toArray();
        $this->merks = \App\Models\Motors::query()->pluck('merk')->unique()->sort()->values()->toArray();
    }

    public function refreshTable(): void { $this->resetPage(); }

    public function getRowsProperty(): LengthAwarePaginator
    {
        return \App\Models\Pembayaran::with(['penyewaan.motor.owner', 'penyewaan.user'])
            ->when($this->search, function ($query) {
                $query->whereHas('penyewaan', function ($q) {
                    $q->where('ID_Penyewaan', 'like', "%{$this->search}%");
                })->orWhereHas('penyewaan.motor', function ($q) {
                    $q->where('merk', 'like', "%{$this->search}%")
                      ->orWhere('no_plat', 'like', "%{$this->search}%");
                });
            })
            ->when($this->merk, fn($q) => $q->whereHas('penyewaan.motor', fn($m) => $m->where('merk', $this->merk)))
            ->when($this->statusTransaksi, fn($q) => $q->where('status', $this->statusTransaksi))
            ->when($this->metode, fn($q) => $q->where('metode_pembayaran', $this->metode))
            ->orderBy($this->sortBy['column'] === 'tanggal' ? 'created_at' : $this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function setStatus(string $status): void { $this->statusTransaksi = $status; $this->resetPage(); }
    public function setMetode(string $metode): void { $this->metode = $metode; $this->resetPage(); }
    public function setMerk(string $merk): void { $this->merk = $merk; $this->resetPage(); }
}; ?>

<div>
    <x-header title="Riwayat Transaksi" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input icon="o-magnifying-glass" placeholder="Cari kode / motor..." wire:model.live.debounce.500ms="search" />
        </x-slot:middle>
        <x-slot:actions>
            <x-dropdown label="Status">
                <x-menu-item icon="o-x-circle" title="Semua" wire:click="setStatus('')" />
                <x-menu-separator />
                <x-menu-item title="Paid" wire:click="setStatus('paid')" />
                <x-menu-item title="Unpaid" wire:click="setStatus('unpaid')" />
                <x-menu-item title="Failed" wire:click="setStatus('failed')" />
            </x-dropdown>
            <x-dropdown label="Metode">
                <x-menu-item icon="o-x-circle" title="Semua" wire:click="setMetode('')" />
                <x-menu-separator />
                @foreach($metodeOptions as $m)
                    <x-menu-item title="{{ $m }}" wire:click="setMetode('{{ $m }}')" />
                @endforeach
            </x-dropdown>
            <x-dropdown label="Merk">
                <x-menu-item icon="o-x-circle" title="Hapus Filter" wire:click="setMerk('')" />
                <x-menu-separator />
                @foreach ($merks as $m)
                    <x-menu-item title="{{ $m }}" wire:click="setMerk('{{ $m }}')" />
                @endforeach
            </x-dropdown>
        </x-slot:actions>
    </x-header>

    <x-table :headers="$headers" :rows="$this->rows" :sort-by="$sortBy" :per-page="$perPage" :per-page-values="[5,10,20,50]" with-pagination>
        @scope('cell_no', $row, $loop)
            {{ ($this->rows->currentPage() - 1) * $this->rows->perPage() + $loop->iteration }}
        @endscope
        @scope('cell_kode', $row)
            #{{ $row->penyewaan->ID_Penyewaan ?? '-' }}
        @endscope
        @scope('cell_tanggal', $row)
            {{ \Carbon\Carbon::parse($row->tanggal_bayar ?? $row->created_at)->format('d/m/Y') }}
        @endscope
        @scope('cell_motor', $row)
            {{ $row->penyewaan->motor->merk ?? '-' }} ({{ $row->penyewaan->motor->no_plat ?? '-' }})
        @endscope
        @scope('cell_penyewa', $row)
            {{ $row->penyewaan->user->nama ?? '-' }}
        @endscope
        @scope('cell_metode_pembayaran', $row)
            {{ $row->metode_pembayaran }}
        @endscope
        @scope('cell_status', $row)
            @if ($row->status === 'paid')
                <x-badge value="Paid" class="badge badge-success badge-soft" />
            @elseif ($row->status === 'unpaid')
                <x-badge value="Unpaid" class="badge badge-error badge-soft" />
            @else
                <x-badge value="Failed" class="badge badge-warning badge-soft" />
            @endif
        @endscope
        @scope('cell_harga', $row)
            Rp {{ number_format($row->jumlah_bayar ?? 0, 0, ',', '.') }}
        @endscope
        @scope('cell_actions', $row)
            <x-dropdown>
                <x-slot:trigger>
                    <x-button icon="m-ellipsis-vertical" class="btn-circle" />
                </x-slot:trigger>
                <x-menu-item title="Detail" icon="o-eye" link="/admin/history/detail/{{ $row->ID_Pembayaran }}" />
            </x-dropdown>
        @endscope
        <x-slot:empty>
            <x-icon name="o-archive-box" label="Data transaksi tidak ditemukan." />
        </x-slot:empty>
    </x-table>
</div>
