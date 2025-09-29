<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use App\Models\Penyewaan;
use App\Models\Motors;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\DB;

new class extends Component {
    use WithPagination, Toast;
    
    protected $listeners = ['refresh' => 'refreshTable'];
    
    public int $perPage = 10;
    public array $sortBy = ['column' => 'tanggal_pengajuan_pengembalian', 'direction' => 'desc'];
    public array $headers = [
        ['key' => 'no', 'label' => 'No', 'sortable' => false],
        ['key' => 'kode', 'label' => 'Kode Booking', 'sortable' => true],
        ['key' => 'penyewa', 'label' => 'Penyewa', 'sortable' => true],
        ['key' => 'motor', 'label' => 'Motor', 'sortable' => true],
        ['key' => 'periode', 'label' => 'Periode Sewa', 'sortable' => false],
        ['key' => 'tanggal_pengajuan', 'label' => 'Tgl Pengajuan', 'sortable' => true],
        ['key' => 'catatan', 'label' => 'Catatan', 'sortable' => false],
        ['key' => 'actions', 'label' => 'Aksi', 'sortable' => false],
    ];

    public string $search = '';

    public function refreshTable(): void 
    { 
        $this->resetPage(); 
    }

    public function getRowsProperty()
    {
        return Penyewaan::with(['user', 'motor'])
            ->where('status', 'menunggu_konfirmasi_pengembalian')
            ->when($this->search, function ($query) {
                $query->where('ID_Penyewaan', 'like', "%{$this->search}%")
                      ->orWhereHas('user', function ($q) {
                          $q->where('nama', 'like', "%{$this->search}%")
                            ->orWhere('email', 'like', "%{$this->search}%");
                      })
                      ->orWhereHas('motor', function ($q) {
                          $q->where('merk', 'like', "%{$this->search}%")
                            ->orWhere('no_plat', 'like', "%{$this->search}%");
                      });
            })
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function confirmReturn($bookingId)
    {
        try {
            DB::beginTransaction();

            $booking = Penyewaan::with('motor')->findOrFail($bookingId);
            
            if ($booking->status !== 'menunggu_konfirmasi_pengembalian') {
                throw new \Exception('Status booking tidak valid untuk dikonfirmasi.');
            }

            // Update status booking menjadi completed
            $booking->update([
                'status' => 'completed',
                'tanggal_pengembalian' => now()
            ]);
            
            // Update status motor menjadi tersedia
            if ($booking->motor) {
                $booking->motor->update(['status' => 'tersedia']);
            }

            DB::commit();

            $this->success('Pengembalian motor berhasil dikonfirmasi!');
            $this->dispatch('refresh');

        } catch (\Exception $e) {
            DB::rollback();
            $this->error('Gagal konfirmasi pengembalian: ' . $e->getMessage());
        }
    }

    public function rejectReturn($bookingId)
    {
        try {
            $booking = Penyewaan::findOrFail($bookingId);
            
            if ($booking->status !== 'menunggu_konfirmasi_pengembalian') {
                throw new \Exception('Status booking tidak valid untuk ditolak.');
            }

            // Kembalikan ke status disewa
            $booking->update([
                'status' => 'disewa',
                'catatan_pengembalian' => null,
                'tanggal_pengajuan_pengembalian' => null
            ]);

            $this->warning('Pengajuan pengembalian ditolak. Status kembali ke "Disewa".');
            $this->dispatch('refresh');

        } catch (\Exception $e) {
            $this->error('Gagal menolak pengembalian: ' . $e->getMessage());
        }
    }
}; ?>

<div>
    <x-header title="Konfirmasi Pengembalian Motor" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input icon="o-magnifying-glass" placeholder="Cari booking / penyewa / motor..." wire:model.live.debounce.500ms="search" />
        </x-slot:middle>
        <x-slot:actions>
            <x-badge value="Menunggu Konfirmasi: {{ $this->rows->total() }}" class="badge badge-info" />
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
        
        @scope('cell_penyewa', $row)
            <div class="flex items-center gap-2">
                <x-icon name="o-user" class="w-4 h-4 text-gray-400" />
                <div>
                    <div class="font-medium">{{ $row->user->nama ?? '-' }}</div>
                    <div class="text-sm text-gray-500">{{ $row->user->email ?? '-' }}</div>
                </div>
            </div>
        @endscope
        
        @scope('cell_motor', $row)
            <div class="flex items-center gap-3">
                <x-icon name="o-truck" class="w-5 h-5 text-gray-400" />
                <div>
                    <div class="font-medium">{{ $row->motor->merk ?? '-' }}</div>
                    <div class="text-sm text-gray-500">{{ $row->motor->no_plat ?? '-' }}</div>
                </div>
            </div>
        @endscope
        
        @scope('cell_periode', $row)
            <div class="text-sm">
                <div class="flex items-center gap-2">
                    <x-icon name="o-calendar-days" class="w-4 h-4 text-blue-500" />
                    <span class="text-blue-600">{{ $row->tanggal_mulai }}</span>
                </div>
                <div class="text-gray-500 mt-1">
                    sampai {{ $row->tanggal_selesai }}
                </div>
            </div>
        @endscope
        
        @scope('cell_tanggal_pengajuan', $row)
            <div class="flex items-center gap-2">
                <x-icon name="o-clock" class="w-4 h-4 text-orange-500" />
                <span class="text-sm">
                    {{ \Carbon\Carbon::parse($row->tanggal_pengajuan_pengembalian)->format('d/m/Y H:i') }}
                </span>
            </div>
        @endscope
        
        @scope('cell_catatan', $row)
            <div class="max-w-xs">
                <p class="text-sm text-gray-700 truncate" title="{{ $row->catatan_pengembalian }}">
                    {{ $row->catatan_pengembalian ?? '-' }}
                </p>
            </div>
        @endscope
        
        @scope('cell_actions', $row)
            <div class="flex items-center gap-2">
                <x-button 
                    icon="o-check-circle" 
                    wire:click="confirmReturn({{ $row->ID_Penyewaan }})"
                    wire:confirm="Konfirmasi pengembalian motor ini? Motor akan tersedia kembali."
                    class="btn-success btn-sm"
                    tooltip="Konfirmasi Pengembalian"
                />
                <x-button 
                    icon="o-x-circle" 
                    wire:click="rejectReturn({{ $row->ID_Penyewaan }})"
                    wire:confirm="Tolak pengajuan pengembalian ini? Status akan kembali ke 'Disewa'."
                    class="btn-error btn-sm"
                    tooltip="Tolak Pengembalian"
                />
                <x-button 
                    icon="o-eye" 
                    link="/admin/bookings/detail/{{ $row->ID_Penyewaan }}"
                    class="btn-sm"
                    tooltip="Detail Booking"
                />
            </div>
        @endscope

        <x-slot:empty>
            <div class="flex flex-col items-center justify-center py-12">
                <x-icon name="o-check-circle" class="w-16 h-16 text-gray-300 mb-4" />
                <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada Pengajuan Pengembalian</h3>
                <p class="text-gray-500">Semua pengajuan pengembalian telah diproses.</p>
            </div>
        </x-slot:empty>
    </x-table>
</div>
