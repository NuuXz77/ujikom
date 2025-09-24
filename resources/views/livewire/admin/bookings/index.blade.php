<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;

new class extends Component {
    use WithPagination;

    public int $perPage = 10;
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];
    public array $headers = [
        ['key' => 'no', 'label' => 'No', 'sortable' => false],
        ['key' => 'kode', 'label' => 'Kode', 'sortable' => true],
        ['key' => 'penyewa', 'label' => 'Penyewa', 'sortable' => true],
        ['key' => 'motor', 'label' => 'Motor', 'sortable' => true],
        ['key' => 'tanggal_mulai', 'label' => 'Mulai', 'sortable' => true],
        ['key' => 'tanggal_selesai', 'label' => 'Selesai', 'sortable' => true],
        ['key' => 'tipe_durasi', 'label' => 'Durasi', 'sortable' => true],
        ['key' => 'status', 'label' => 'Status', 'sortable' => true],
        ['key' => 'harga', 'label' => 'Harga', 'sortable' => true],
        ['key' => 'actions', 'label' => 'Aksi', 'sortable' => false],
    ];

    public string $search = '';
    public string $status = '';
    public string $durasi = '';
    public string $merk = '';
    public array $durasiOptions = [];
    public array $statusOptions = ['pending','active','completed','canceled'];
    public array $merks = [];

    public function mount(): void
    {
        $this->durasiOptions = \App\Models\Penyewaan::query()->pluck('tipe_durasi')->unique()->sort()->values()->toArray();
        $this->merks = \App\Models\Motors::query()->pluck('merk')->unique()->sort()->values()->toArray();
    }

    public function getRowsProperty(): LengthAwarePaginator
    {
        return \App\Models\Penyewaan::with(['motor','user'])
            ->when($this->search, function($q){
                $q->where(function($w){
                    $w->where('ID_Penyewaan','like',"%{$this->search}%")
                      ->orWhere('tipe_durasi','like',"%{$this->search}%");
                })
                ->orWhereHas('motor', function($m){
                    $m->where('merk','like',"%{$this->search}%")
                       ->orWhere('no_plat','like',"%{$this->search}%");
                })
                ->orWhereHas('user', function($u){
                    $u->where('nama','like',"%{$this->search}%")
                       ->orWhere('email','like',"%{$this->search}%");
                });
            })
            ->when($this->status, fn($q) => $q->where('status',$this->status))
            ->when($this->durasi, fn($q) => $q->where('tipe_durasi',$this->durasi))
            ->when($this->merk, fn($q) => $q->whereHas('motor', fn($m) => $m->where('merk',$this->merk)))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function setStatus(string $status): void { $this->status = $status; $this->resetPage(); }
    public function setDurasi(string $durasi): void { $this->durasi = $durasi; $this->resetPage(); }
    public function setMerk(string $merk): void { $this->merk = $merk; $this->resetPage(); }
}; ?>

<div>
    <x-header title="Penyewaan (Bookings)" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input icon="o-magnifying-glass" placeholder="Cari kode / motor / penyewa..." wire:model.live.debounce.500ms="search" />
        </x-slot:middle>
        <x-slot:actions>
            <x-dropdown label="Status">
                <x-menu-item icon="o-x-circle" title="Semua" wire:click="setStatus('')" />
                <x-menu-separator />
                @foreach($statusOptions as $s)
                    <x-menu-item title="{{ ucfirst($s) }}" wire:click="setStatus('{{ $s }}')" />
                @endforeach
            </x-dropdown>
            <x-dropdown label="Durasi">
                <x-menu-item icon="o-x-circle" title="Semua" wire:click="setDurasi('')" />
                <x-menu-separator />
                @foreach($durasiOptions as $d)
                    <x-menu-item title="{{ ucfirst($d) }}" wire:click="setDurasi('{{ $d }}')" />
                @endforeach
            </x-dropdown>
            <x-dropdown label="Merk">
                <x-menu-item icon="o-x-circle" title="Hapus Filter" wire:click="setMerk('')" />
                <x-menu-separator />
                @foreach($merks as $m)
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
            #{{ $row->ID_Penyewaan }}
        @endscope
        @scope('cell_penyewa', $row)
            {{ $row->user->nama ?? '-' }}
        @endscope
        @scope('cell_motor', $row)
            {{ $row->motor->merk ?? '-' }} ({{ $row->motor->no_plat ?? '-' }})
        @endscope
        @scope('cell_tanggal_mulai', $row)
            {{ \Carbon\Carbon::parse($row->tanggal_mulai)->format('d/m/Y') }}
        @endscope
        @scope('cell_tanggal_selesai', $row)
            {{ \Carbon\Carbon::parse($row->tanggal_selesai)->format('d/m/Y') }}
        @endscope
        @scope('cell_tipe_durasi', $row)
            {{ ucfirst($row->tipe_durasi) }}
        @endscope
        @scope('cell_status', $row)
            @php($status = $row->status)
            @if ($status === 'pending')
                <x-badge value="Pending" class="badge badge-warning badge-soft" />
            @elseif ($status === 'active')
                <x-badge value="Active" class="badge badge-primary badge-soft" />
            @elseif ($status === 'dibayar')
                <x-badge value="Dibayar" class="badge badge-success badge-soft" />
            @else
                <x-badge value="Canceled" class="badge badge-error badge-soft" />
            @endif
        @endscope
        @scope('cell_harga', $row)
            Rp {{ number_format($row->harga, 0, ',', '.') }}
        @endscope
        @scope('cell_actions', $row)
            <x-dropdown>
                <x-slot:trigger>
                    <x-button icon="m-ellipsis-vertical" class="btn-circle" />
                </x-slot:trigger>
                <x-menu-item title="Detail" icon="o-eye" link="/admin/bookings/detail/{{ $row->ID_Penyewaan }}" />
                @if($row->status === 'pending')
                    <x-menu-item title="Aktifkan" icon="o-check" wire:click="$dispatch('activateBooking', { id: {{ $row->ID_Penyewaan }} })" />
                @endif
                @if(in_array($row->status, ['active']))
                    <x-menu-item title="Selesaikan" icon="o-check-circle" wire:click="$dispatch('completeBooking', { id: {{ $row->ID_Penyewaan }} })" />
                @endif
                @if(in_array($row->status, ['pending','active']))
                    <x-menu-item title="Batalkan" icon="o-x-mark" wire:click="$dispatch('cancelBooking', { id: {{ $row->ID_Penyewaan }} })" class="text-red-500" />
                @endif
            </x-dropdown>
        @endscope
        <x-slot:empty>
            <x-icon name="o-clipboard-document-list" label="Belum ada penyewaan." />
        </x-slot:empty>
    </x-table>
</div>
