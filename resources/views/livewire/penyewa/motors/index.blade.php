<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Penyewaan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

new class extends Component {
    use WithPagination;
    
    protected $listeners = ['refresh' => 'refreshTable'];
    
    public int $perPage = 10;
    public array $sortBy = ['column' => 'created_at', 'direction' => 'desc'];
    public array $headers = [
        ['key' => 'no', 'label' => 'No', 'sortable' => false],
        ['key' => 'kode', 'label' => 'Kode Booking', 'sortable' => true],
        ['key' => 'motor', 'label' => 'Motor', 'sortable' => true],
        ['key' => 'plat', 'label' => 'Plat', 'sortable' => false],
        ['key' => 'periode_sewa', 'label' => 'Periode Sewa', 'sortable' => false],
        ['key' => 'durasi', 'label' => 'Durasi', 'sortable' => false],
        ['key' => 'harga', 'label' => 'Total Bayar', 'sortable' => true],
        ['key' => 'status', 'label' => 'Status', 'sortable' => true],
        ['key' => 'actions', 'label' => 'Aksi', 'sortable' => false],
    ];

    public string $search = '';
    public string $status = '';
    public string $merk = '';
    public array $merks = [];
    public bool $showExpired = true;

    public function mount(): void
    {
        $userId = Auth::id();
        
        // Ambil merk motor yang pernah disewa user yang login
        $this->merks = \App\Models\Motors::whereHas('penyewaan', function($q) use ($userId) {
            $q->where('penyewa_id', $userId);
        })->pluck('merk')->unique()->sort()->values()->toArray();
    }

    public function refreshTable(): void 
    { 
        $this->resetPage(); 
    }

    public function setStatus($s){ 
        $this->status = $s; 
        $this->resetPage();
    }
    
    public function setMerk(string $merk): void 
    { 
        $this->merk = $merk; 
        $this->resetPage(); 
    }
    
    public function toggleExpired(){ 
        $this->showExpired = !$this->showExpired; 
        $this->resetPage();
    }

    public function getRowsProperty(): LengthAwarePaginator
    {
        $userId = Auth::id();
        
        $query = Penyewaan::with(['motor'])
            ->where('penyewa_id', $userId)
            ->when($this->search, function ($query) {
                $query->where('ID_Penyewaan', 'like', "%{$this->search}%")
                      ->orWhereHas('motor', function ($q) {
                          $q->where('merk', 'like', "%{$this->search}%")
                            ->orWhere('no_plat', 'like', "%{$this->search}%");
                      });
            })
            ->when($this->merk, fn($q) => $q->whereHas('motor', fn($m) => $m->where('merk', $this->merk)))
            ->when($this->status, fn($q) => $q->where('status', $this->status));

        // Filter expired jika tidak ingin ditampilkan
        if (!$this->showExpired) {
            $query->where('tanggal_selesai', '>=', now()->toDateString());
        }

        return $query->orderBy($this->sortBy['column'], $this->sortBy['direction'])
                     ->paginate($this->perPage);
    }

}; ?>

<div class="space-y-6">
    <x-header title="Status Sewa Saya" separator>
        <x-slot:middle class="!justify-end">
            <x-input icon="o-magnifying-glass" placeholder="Cari kode / motor / plat" wire:model.live.debounce.500ms="search" />
        </x-slot:middle>
        <x-slot:actions>
            <x-dropdown label="Status" icon="o-funnel">
                <x-menu-item icon="o-x-circle" title="Semua Status" wire:click="setStatus('')" />
                <x-menu-separator />
                <x-menu-item title="Pending" wire:click="setStatus('pending')" />
                <x-menu-item title="Dibayar" wire:click="setStatus('dibayar')" />
                <x-menu-item title="Disewa" wire:click="setStatus('disewa')" />
                <x-menu-item title="Menunggu Konfirmasi" wire:click="setStatus('menunggu_konfirmasi_pengembalian')" />
                <x-menu-item title="Selesai" wire:click="setStatus('completed')" />
                <x-menu-item title="Dibatalkan" wire:click="setStatus('dibatalkan')" />
            </x-dropdown>
            
            <x-dropdown label="Motor" icon="o-truck">
                <x-menu-item icon="o-x-circle" title="Semua Motor" wire:click="setMerk('')" />
                <x-menu-separator />
                @foreach ($merks as $m)
                    <x-menu-item title="{{ $m }}" wire:click="setMerk('{{ $m }}')" />
                @endforeach
            </x-dropdown>

                <x-button label="Kembali" icon="o-arrow-left" link="/dashboard" class="btn-ghost" />

{{--             
            <x-button :class="$showExpired ? 'btn-warning btn-soft':''" wire:click="toggleExpired" icon="o-clock">
                @if($showExpired) Sembunyi Kadaluarsa @else Tampilkan Kadaluarsa @endif
            </x-button> --}}
        </x-slot:actions>
    </x-header>

    <x-table :headers="$headers" :rows="$this->rows" :sort-by="$sortBy" :per-page="$perPage" :per-page-values="[5,10,20,50]" with-pagination>
        @scope('cell_no', $row, $loop)
            {{ ($this->rows->currentPage() - 1) * $this->rows->perPage() + $loop->iteration }}
        @endscope
        
        @scope('cell_kode', $row)
            <div class="flex items-center gap-2">
                <x-icon name="o-hashtag" class="w-4 h-4 text-gray-400" />
                <span class="font-medium">#{{ $row->ID_Penyewaan }}</span>
            </div>
        @endscope
        
        @scope('cell_motor', $row)
            <div class="flex items-center gap-3">
                <x-icon name="o-truck" class="w-5 h-5 text-gray-400" />
                <div>
                    <div class="font-medium">{{ $row->motor->merk ?? '-' }}</div>
                </div>
            </div>
        @endscope
        
        @scope('cell_plat', $row)
            <div class="flex items-center gap-2">
                <x-icon name="o-identification" class="w-4 h-4 text-gray-400" />
                <span>{{ $row->motor->no_plat ?? '-' }}</span>
            </div>
        @endscope
        
        @scope('cell_periode_sewa', $row)
            @php
                $expired = \Carbon\Carbon::parse($row->tanggal_selesai)->isPast();
            @endphp
            <div class="text-sm">
                <div class="flex items-center gap-2">
                    <x-icon name="o-calendar-days" class="w-4 h-4 {{ $expired ? 'text-red-500' : 'text-blue-500' }}" />
                    <span class="{{ $expired ? 'text-red-600' : 'text-blue-600' }}">{{ $row->tanggal_mulai }}</span>
                </div>
                <div class="text-gray-500 mt-1 ml-6">
                    sampai {{ $row->tanggal_selesai }}
                </div>
                @if($expired)
                    <div class="text-xs text-red-500 mt-1 ml-6 font-medium">
                        KADALUARSA
                    </div>
                @endif
            </div>
        @endscope
        
        @scope('cell_durasi', $row)
            <span class="capitalize">{{ $row->tipe_durasi }}</span>
        @endscope
        
        @scope('cell_harga', $row)
            <div class="flex items-center gap-2">
                <x-icon name="o-currency-dollar" class="w-4 h-4 text-green-500" />
                <div class="font-bold text-green-600">
                    Rp {{ number_format($row->harga, 0, ',', '.') }}
                </div>
            </div>
        @endscope
        
        @scope('cell_status', $row)
            @php
                $expired = \Carbon\Carbon::parse($row->tanggal_selesai)->isPast();
                $displayStatus = $expired && $row->status !== 'completed' && $row->status !== 'dibatalkan' ? 'kadaluarsa' : $row->status;
            @endphp
            
            @if($displayStatus === 'pending')
                <x-badge value="Pending" class="badge badge-warning badge-soft" />
            @elseif($displayStatus === 'dibayar')
                <x-badge value="Dibayar" class="badge badge-info badge-soft" />
            @elseif($displayStatus === 'disewa')
                <x-badge value="Disewa" class="badge badge-primary badge-soft" />
            @elseif($displayStatus === 'menunggu_verifikasi_pengembalian')
                <x-badge value="Menunggu Konfirmasi" class="badge badge-info badge-soft" />
            @elseif($displayStatus === 'selesai')
                <x-badge value="Selesai" class="badge badge-success badge-soft" />
            @elseif($displayStatus === 'dibatalkan')
                <x-badge value="Dibatalkan" class="badge badge-error badge-soft" />
            @elseif($displayStatus === 'kadaluarsa')
                <x-badge value="Kadaluarsa" class="badge badge-error badge-soft" />
            @else
                <x-badge value="{{ ucfirst($displayStatus) }}" class="badge badge-soft" />
            @endif
        @endscope
        
        @scope('cell_actions', $row)
            @php
                $expired = \Carbon\Carbon::parse($row->tanggal_selesai)->isPast();
                $displayStatus = $expired && $row->status !== 'completed' && $row->status !== 'dibatalkan' ? 'kadaluarsa' : $row->status;
            @endphp
            
            <x-dropdown>
                <x-slot:trigger>
                    <x-button icon="o-ellipsis-vertical" class="btn-circle btn-ghost btn-sm" />
                </x-slot:trigger>
                
                {{-- Detail selalu ada --}}
                <x-menu-item title="Detail" icon="o-eye" link="/bookings/detail/{{ $row->ID_Penyewaan }}" />
                
                {{-- Jika status = pending, ada opsi edit, hapus, dan bayar --}}
                @if($displayStatus === 'pending')
                    <x-menu-separator />
                    <x-menu-item title="Edit" icon="o-pencil" link="/bookings/edit/{{ $row->ID_Penyewaan }}" />
                    <x-menu-item title="Hapus" icon="o-trash"
                    wire:click="$dispatch('showDeleteModal', { id: {{ $row->ID_Penyewaan }} })" class="text-red-500" />
                    <x-menu-separator />
                    <x-menu-item title="Bayar" icon="o-credit-card" link="/payments/{{ $row->ID_Penyewaan }}" class="text-green-600" />
                @endif
                
                {{-- Jika status = dibayar atau disewa, ada opsi kembalikan motor --}}
                @if(in_array($displayStatus, ['dibayar', 'disewa']))
                    <x-menu-separator />
                    <x-menu-item title="Ajukan Pengembalian" icon="o-arrow-uturn-left"
                    wire:click="$dispatch('showReturnModal', { id: {{ $row->ID_Penyewaan }} })" class="text-red-500" />
                @endif
                
                {{-- Jika status = menunggu konfirmasi, tampilkan info --}}
                @if($displayStatus === 'menunggu_konfirmasi_pengembalian')
                    <x-menu-separator />
                    <x-menu-item 
                        title="Menunggu Konfirmasi Admin" 
                        icon="o-clock" 
                        disabled
                        class="text-blue-600" 
                    />
                @endif
                
                {{-- Jika status selain pending, ada opsi cancel jika belum expired --}}
                @if($displayStatus !== 'pending' && !$expired && !in_array($displayStatus, ['selesai', 'dibatalkan']))
                    <x-menu-separator />
                    <x-menu-item 
                        title="Batalkan" 
                        icon="o-x-circle" 
                        @click="$dispatch('showCancelModal', {{ $row->ID_Penyewaan }})"
                        class="text-red-500" 
                    />
                @endif
            </x-dropdown>
        @endscope

        <x-slot:empty>
            <div class="flex flex-col items-center justify-center py-12">
                <x-icon name="o-cube" class="w-16 h-16 text-gray-300 mb-4" />
                <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada data sewa</h3>
                <p class="text-gray-500 mb-4">Anda belum memiliki riwayat penyewaan motor.</p>
                <x-button icon="o-truck" class="btn-primary" link="/motors">
                    Mulai Sewa Motor
                </x-button>
            </div>
        </x-slot:empty>
    </x-table>
    
    {{-- Modal Components --}}
    <livewire:penyewa.motors.cancel/>
    <livewire:penyewa.motors.return/>
    <livewire:penyewa.motors.delete/>
</div>
