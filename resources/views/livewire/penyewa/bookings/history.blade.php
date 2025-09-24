<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithPagination;

    protected $listeners = ['refresh' => 'refreshTable'];

    public int $perPage = 10;
    public array $sortBy = ['column' => 'tanggal', 'direction' => 'desc'];
    public array $headers = [
        ['key' => 'no', 'label' => 'No', 'sortable' => false],
        ['key' => 'kode', 'label' => 'Kode Sewa', 'sortable' => true],
        ['key' => 'tanggal', 'label' => 'Tanggal', 'sortable' => true],
        ['key' => 'motor', 'label' => 'Motor', 'sortable' => true],
        ['key' => 'pemilik', 'label' => 'Pemilik', 'sortable' => true],
        ['key' => 'periode_sewa', 'label' => 'Periode Sewa', 'sortable' => false],
        ['key' => 'metode_pembayaran', 'label' => 'Metode', 'sortable' => true],
        ['key' => 'status', 'label' => 'Status', 'sortable' => true],
        ['key' => 'harga', 'label' => 'Total Bayar', 'sortable' => true],
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
        $userId = Auth::id();
        
        // Hanya ambil metode pembayaran dari transaksi milik user yang login
        $this->metodeOptions = \App\Models\Transaksi::whereHas('penyewaan', function($q) use ($userId) {
            $q->where('penyewa_id', $userId);
        })->pluck('metode_pembayaran')->unique()->sort()->values()->toArray();

        // Hanya ambil merk motor yang pernah disewa user yang login
        $this->merks = \App\Models\Motors::whereHas('penyewaans', function($q) use ($userId) {
            $q->where('penyewa_id', $userId);
        })->pluck('merk')->unique()->sort()->values()->toArray();
    }

    public function refreshTable(): void 
    { 
        $this->resetPage(); 
    }

    public function getRowsProperty(): LengthAwarePaginator
    {
        $userId = Auth::id();
        
        return \App\Models\Transaksi::with(['penyewaan.motor.owner', 'penyewaan.user'])
            // Filter hanya transaksi milik penyewa yang login
            ->whereHas('penyewaan', function($q) use ($userId) {
                $q->where('penyewa_id', $userId);
            })
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
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function setStatus(string $status): void 
    { 
        $this->statusTransaksi = $status; 
        $this->resetPage(); 
    }
    
    public function setMetode(string $metode): void 
    { 
        $this->metode = $metode; 
        $this->resetPage(); 
    }
    
    public function setMerk(string $merk): void 
    { 
        $this->merk = $merk; 
        $this->resetPage(); 
    }
}; ?>

<div>
    <x-header title="Riwayat Transaksi Saya" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input icon="o-magnifying-glass" placeholder="Cari kode / motor..." wire:model.live.debounce.500ms="search" />
        </x-slot:middle>
        <x-slot:actions>
            <x-dropdown label="Status" icon="o-funnel">
                <x-menu-item icon="o-x-circle" title="Semua Status" wire:click="setStatus('')" />
                <x-menu-separator />
                <x-menu-item title="Berhasil" wire:click="setStatus('completed')" />
                <x-menu-item title="Pending" wire:click="setStatus('pending')" />
                <x-menu-item title="Gagal" wire:click="setStatus('failed')" />
            </x-dropdown>
            
            <x-dropdown label="Metode" icon="o-credit-card">
                <x-menu-item icon="o-x-circle" title="Semua Metode" wire:click="setMetode('')" />
                <x-menu-separator />
                @foreach($metodeOptions as $m)
                    <x-menu-item title="{{ ucfirst(str_replace('_', ' ', $m)) }}" wire:click="setMetode('{{ $m }}')" />
                @endforeach
            </x-dropdown>
            
            <x-dropdown label="Motor" icon="o-truck">
                <x-menu-item icon="o-x-circle" title="Semua Motor" wire:click="setMerk('')" />
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
            <div class="flex items-center gap-2">
                <x-icon name="o-hashtag" class="w-4 h-4 text-gray-400" />
                <span class="font-medium">#{{ $row->penyewaan->ID_Penyewaan ?? '-' }}</span>
            </div>
        @endscope
        
        @scope('cell_tanggal', $row)
            <div class="flex items-center gap-2">
                <x-icon name="o-calendar" class="w-4 h-4 text-gray-400" />
                <span>{{ \Carbon\Carbon::parse($row->tanggal)->format('d/m/Y H:i') }}</span>
            </div>
        @endscope
        
        @scope('cell_motor', $row)
            <div class="flex items-center gap-3">
                <x-icon name="o-truck" class="w-5 h-5 text-gray-400" />
                <div>
                    <div class="font-medium">{{ $row->penyewaan->motor->merk ?? '-' }}</div>
                    <div class="text-sm text-gray-500">{{ $row->penyewaan->motor->no_plat ?? '-' }}</div>
                </div>
            </div>
        @endscope
        
        @scope('cell_pemilik', $row)
            <div class="flex items-center gap-2">
                <x-icon name="o-user" class="w-4 h-4 text-gray-400" />
                <span>{{ $row->penyewaan->motor->owner->nama ?? '-' }}</span>
            </div>
        @endscope
        
        @scope('cell_periode_sewa', $row)
            <div class="text-sm">
                <div class="flex items-center gap-2">
                    <x-icon name="o-calendar-days" class="w-4 h-4 text-blue-500" />
                    <span class="text-blue-600">{{ $row->penyewaan->tanggal_mulai ?? '-' }}</span>
                </div>
                <div class="text-gray-500 mt-1 ml-6">
                    sampai {{ $row->penyewaan->tanggal_selesai ?? '-' }}
                </div>
                <div class="text-xs text-gray-400 mt-1 ml-6">
                    {{ ucfirst($row->penyewaan->tipe_durasi ?? '-') }}
                </div>
            </div>
        @endscope
        
        @scope('cell_metode_pembayaran', $row)
            <div class="flex items-center gap-2">
                @if(str_contains($row->metode_pembayaran, 'va'))
                    <x-icon name="o-building-library" class="w-4 h-4 text-blue-500" />
                @elseif(in_array($row->metode_pembayaran, ['ovo', 'gopay', 'dana']))
                    <x-icon name="o-device-phone-mobile" class="w-4 h-4 text-green-500" />
                @elseif($row->metode_pembayaran == 'cash')
                    <x-icon name="o-banknotes" class="w-4 h-4 text-yellow-500" />
                @else
                    <x-icon name="o-credit-card" class="w-4 h-4 text-gray-400" />
                @endif
                <span class="capitalize">{{ str_replace('_', ' ', $row->metode_pembayaran) }}</span>
            </div>
        @endscope
        
        @scope('cell_status', $row)
            @if ($row->status === 'completed')
                <x-badge value="Berhasil" class="badge badge-success badge-soft" />
            @elseif ($row->status === 'pending')
                <x-badge value="Pending" class="badge badge-warning badge-soft" />
            @elseif ($row->status === 'failed')
                <x-badge value="Gagal" class="badge badge-error badge-soft" />
            @else
                <x-badge value="{{ ucfirst($row->status) }}" class="badge badge-soft" />
            @endif
        @endscope
        
        @scope('cell_harga', $row)
            <div class="flex items-center gap-2">
                <x-icon name="o-currency-dollar" class="w-4 h-4 text-green-500" />
                <div>
                    <div class="font-bold text-green-600">
                        Rp {{ number_format($row->jumlah ?? 0, 0, ',', '.') }}
                    </div>
                    <div class="text-xs text-gray-500">
                        Total: Rp {{ number_format($row->penyewaan->harga ?? 0, 0, ',', '.') }}
                    </div>
                </div>
            </div>
        @endscope
        
        @scope('cell_actions', $row)
            <x-dropdown>
                <x-slot:trigger>
                    <x-button icon="o-ellipsis-vertical" class="btn-circle btn-ghost btn-sm" />
                </x-slot:trigger>
                <x-menu-item title="Detail Transaksi" icon="o-eye" link="/history/detail/{{ $row->ID_Transaksi }}" />
                <x-menu-item title="Detail Booking" icon="o-clipboard-document-list" link="/bookings/detail/{{ $row->penyewaan->ID_Penyewaan ?? '' }}" />
                @if($row->status === 'completed')
                    <x-menu-separator />
                    <x-menu-item title="Cetak Invoice" icon="o-printer" link="/history/invoice/{{ $row->ID_Transaksi }}" />
                @endif
            </x-dropdown>
        @endscope
        
        <x-slot:empty>
            <div class="flex flex-col items-center justify-center py-12">
                <x-icon name="o-document-text" class="w-16 h-16 text-gray-300 mb-4" />
                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Riwayat Transaksi</h3>
                <p class="text-gray-500 mb-4">Anda belum pernah melakukan transaksi penyewaan motor.</p>
                <x-button icon="o-truck" class="btn-primary" link="/motors">
                    Mulai Sewa Motor
                </x-button>
            </div>
        </x-slot:empty>
    </x-table>
</div>
