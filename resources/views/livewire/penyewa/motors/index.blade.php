<?php

use Livewire\Volt\Component;
use App\Models\Penyewaan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

new class extends Component {
    protected $listeners = ['refresh' => '$refresh'];
    public array $headers = [
        ['key' => 'kode', 'label' => 'Kode Booking'],
        ['key' => 'motor', 'label' => 'Motor'],
        ['key' => 'plat', 'label' => 'Plat'],
        ['key' => 'mulai', 'label' => 'Mulai'],
        ['key' => 'selesai', 'label' => 'Selesai'],
        ['key' => 'durasi', 'label' => 'Durasi'],
        ['key' => 'status', 'label' => 'Status'],
        ['key' => 'actions', 'label' => 'Aksi'],
    ];

    public string $search = '';
    public string $status = '';// filter manual
    public bool $showExpired = true; // tampilkan yang kadaluarsa

    public function setStatus($s){ $this->status = $s; }
    public function toggleExpired(){ $this->showExpired = !$this->showExpired; }

    public function getRowsProperty()
    {
        $userId = Auth::id();
        // Ambil semua penyewaan user dengan status pending atau disewa
        $query = Penyewaan::with('motor')
            ->where('penyewa_id', $userId)
            ->when($this->status, fn($q)=> $q->where('status', $this->status))
            ->whereIn('status', ['pending','disewa']);

        $rows = $query->get()->map(function($p){
            $expired = Carbon::parse($p->tanggal_selesai)->isPast();
            return [
                'id' => $p->ID_Penyewaan,
                'kode' => '#'.$p->ID_Penyewaan,
                'motor' => $p->motor?->merk,
                'plat' => $p->motor?->no_plat,
                'mulai' => $p->tanggal_mulai,
                'selesai' => $p->tanggal_selesai,
                'durasi' => ucfirst($p->tipe_durasi),
                'status' => $expired ? 'kadaluarsa' : $p->status,
                'expired' => $expired,
            ];
        });

        if(!$this->showExpired){
            $rows = $rows->filter(fn($r)=> !$r['expired']);
        }

        if($this->search){
            $rows = $rows->filter(function($r){
                return str_contains(strtolower($r['motor'] ?? ''), strtolower($this->search))
                    || str_contains(strtolower($r['plat'] ?? ''), strtolower($this->search))
                    || str_contains(strtolower($r['kode']), strtolower($this->search));
            });
        }

        return $rows->values();
    }

}; ?>

<div class="space-y-6">
    <x-header title="Status Sewa Saya" separator>
        <x-slot:middle class="!justify-end">
            <x-input icon="o-magnifying-glass" placeholder="Cari kode / motor / plat" wire:model.live.debounce.500ms="search" />
        </x-slot:middle>
        <x-slot:actions>
            <x-dropdown label="Status">
                <x-menu-item icon="o-x-circle" title="Semua" wire:click="setStatus('')" />
                <x-menu-separator />
                <x-menu-item title="Pending" wire:click="setStatus('pending')" />
                <x-menu-item title="Disewa" wire:click="setStatus('disewa')" />
            </x-dropdown>
            <x-button :class="$showExpired ? 'btn-warning btn-soft':''" wire:click="toggleExpired" icon="o-clock">
                @if($showExpired) Sembunyi Kadaluarsa @else Tampilkan Kadaluarsa @endif
            </x-button>
        </x-slot:actions>
    </x-header>

    <x-table :headers="$headers" :rows="$this->rows">
        @scope('cell_kode', $row)
            {{ $row['kode'] }}
        @endscope
        @scope('cell_motor', $row)
            {{ $row['motor'] ?? '-' }}
        @endscope
        @scope('cell_plat', $row)
            {{ $row['plat'] ?? '-' }}
        @endscope
        @scope('cell_mulai', $row)
            {{ $row['mulai'] }}
        @endscope
        @scope('cell_selesai', $row)
            {{ $row['selesai'] }}
        @endscope
        @scope('cell_durasi', $row)
            {{ $row['durasi'] }}
        @endscope
        @scope('cell_status', $row)
            @if($row['status']==='pending')
                <x-badge value="Pending" class="badge-warning badge-soft" />
            @elseif($row['status']==='disewa')
                <x-badge value="Disewa" class="badge-primary badge-soft" />
            @elseif($row['status']==='kadaluarsa')
                <x-badge value="Kadaluarsa" class="badge-error badge-soft" />
            @endif
        @endscope
        @scope('cell_actions', $row)
            <x-dropdown>
                <x-slot:trigger>
                    <x-button icon="m-ellipsis-vertical" class="btn-circle" />
                </x-slot:trigger>
                <x-menu-item title="Detail" icon="o-eye" link="/payments/{{ $row['id'] }}" />
                <x-menu-item title="Batal" icon="o-x-circle" :disabled="$row['status']!=='pending'" wire:click="$dispatch('showCancelModal', { id: '{{ $row['id'] }}' })" class="text-red-500" />
            </x-dropdown>
        @endscope

        <x-slot:empty>
            <x-icon name="o-cube" label="Tidak ada data sewa." />
        </x-slot:empty>
    </x-table>
    <livewire:penyewa.motors.cancel/>
</div>
