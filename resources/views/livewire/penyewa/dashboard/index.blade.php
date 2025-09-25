<?php

use App\Models\Motors;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithPagination;

    // Filter query params
    #[Url]
    public string $tipe_cc = '';
    #[Url]
    public string $search = '';
    #[Url]
    public string $status = '';
    #[Url]
    public string $merk = '';

    public array $ccOptions = ['100cc', '125cc', '150cc'];
    public array $merks = [];

    public function mount(): void
    {
        // Untuk penyewa: tampilkan semua merk dari seluruh motor (hanya yang bisa disewa optional bisa difilter status)
        $this->merks = Motors::query()->pluck('merk')->unique()->sort()->values()->toArray();
    }

    public function with(): array
    {
        $motorsQuery = Motors::query()->with('tarif');

        $motorsQuery
            ->when($this->search, function ($query) {
                $query->where(function($q) {
                    $q->where('merk', 'like', "%{$this->search}%")
                      ->orWhere('no_plat', 'like', "%{$this->search}%");
                });
            })
            ->when($this->status, function ($query) {
                $query->where('status', $this->status);
            })
            ->when($this->merk, function ($query) {
                $query->where('merk', $this->merk);
            })
            ->when($this->tipe_cc, function ($query) {
                $query->where('tipe_cc', $this->tipe_cc);
            });

        return [
            'motors' => $motorsQuery->paginate(8),
            'ccOptions' => $this->ccOptions,
        ];
    }

    public function setStatus(string $status): void
    {
        $this->status = $status; $this->resetPage();
    }

    public function setMerk(string $merk): void
    {
        $this->merk = $merk; $this->resetPage();
    }

    public function setTypeCC(string $cc): void
    {
        $this->tipe_cc = $cc; $this->resetPage();
    }
}; ?>

<div>
    {{-- HEADER DENGAN PROGRESS INDICATOR BAWAAN MARYUI --}}
    <x-header title="Sewa Motor" subtitle="Pilih motor yang tersedia untuk disewa" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input icon="o-magnifying-glass" placeholder="Cari merk / plat..." wire:model.live.debounce.500ms="search" />
        </x-slot:middle>
        <x-slot:actions>
            {{-- Dropdown untuk Filter Status --}}
            <x-dropdown label="Status">
                <x-menu-item icon="o-x-circle" title="Semua Status" wire:click="setStatus('')" />
                <x-menu-separator />
                <x-menu-item icon="o-check-circle" title="Tersedia" wire:click="setStatus('tersedia')" />
                <x-menu-item icon="o-shopping-cart" title="Sedang Disewa" wire:click="setStatus('disewa')" />
                <x-menu-item icon="o-wrench" title="Perawatan" wire:click="setStatus('perawatan')" />
                <x-menu-item icon="o-clock" title="Verifikasi" wire:click="setStatus('sedang_diverifikasi')" />
            </x-dropdown>

            <x-dropdown label="Merk">
                <x-menu-item icon="o-x-circle" title="Hapus Filter" wire:click="setMerk('')" />
                <x-menu-separator />
                @foreach ($merks as $m)
                    <x-menu-item title="{{ $m }}" wire:click="setMerk('{{ $m }}')" />
                @endforeach
            </x-dropdown>

            <x-dropdown label="CC">
                <x-menu-item icon="o-x-circle" title="Hapus Filter" wire:click="setTypeCC('')" />
                <x-menu-separator />
                @foreach ($ccOptions as $cc)
                    <x-menu-item title="{{ $cc }}" wire:click="setTypeCC('{{ $cc }}')" />
                @endforeach
            </x-dropdown>
        </x-slot:actions>
    </x-header>

    {{-- GRID CONTAINER --}}
    <div class="grid grid-cols-1 gap-6 mt-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">

        {{-- LOOPING DATA MOTOR DARI DATABASE --}}
        @forelse ($motors as $motor)
            <x-card title="{{ $motor->merk }} - {{ $motor->tipe_cc }}" shadow separator>
                {{-- GAMBAR MOTOR --}}
                <x-slot:figure>
                    @php($img = $motor->photo ?? null)
                    <img src="{{ $img ? asset('storage/'.$img) : 'https://placehold.co/500x200/e2e8f0/a0aec0?text=No+Image' }}" class="object-cover w-full h-48" />
                </x-slot:figure>

                {{-- KONTEN CARD --}}
                <div class="flex items-center justify-between">
                    <div>
                        @php($tarif = $motor->tarif)
                        <div class="flex flex-col">
                            <span class="font-bold text-lg">Rp {{ number_format($tarif->tarif_harian ?? 0, 0, ',', '.') }} <span class="text-sm text-gray-500">/hari</span></span>
                            @if($tarif && $tarif->tarif_mingguan)
                                <span class="text-xs text-gray-500">Mingguan: Rp {{ number_format($tarif->tarif_mingguan, 0, ',', '.') }}</span>
                            @endif
                            @if($tarif && $tarif->tarif_bulanan)
                                <span class="text-xs text-gray-500">Bulanan: Rp {{ number_format($tarif->tarif_bulanan, 0, ',', '.') }}</span>
                            @endif
                        </div>
                    </div>
                    <div>
                        @if ($motor->status == 'tersedia')
                            <x-badge value="Tersedia" class="badge badge-success badge-soft" />
                        @elseif ($motor->status == 'disewa')
                            <x-badge value="Disewa" class="badge badge-primary badge-soft" />
                        @elseif ($motor->status == 'sedang_diverifikasi')
                            <x-badge value="Verifikasi" class="badge badge-warning badge-soft" />
                        @elseif ($motor->status == 'dibooking')
                            <x-badge value="Dibooking" class="badge badge-info badge-soft" />
                        @elseif ($motor->status == 'dibayar')
                            <x-badge value="Dibayar" class="badge badge-info badge-soft" />
                        @else
                            <x-badge value="Perawatan" class="badge badge-error badge-soft" />
                        @endif
                    </div>
                </div>

                {{-- TOMBOL AKSI --}}
                <x-slot:actions>
                    @if($motor->status === 'tersedia')
                        <x-button
                            label="Sewa"
                            icon="o-shopping-cart"
                            class="btn-sm btn-primary"
                            link="/bookings/{{ $motor->ID_Motor }}" />
                    @endif
                </x-slot:actions>
            </x-card>
        @empty
            {{-- TAMPILAN JIKA TIDAK ADA DATA --}}
            <div class="col-span-full text-center py-12">
                <x-icon name="o-inbox" class="w-16 h-16 mx-auto text-gray-300" />
                <p class="mt-4 text-gray-500">Data motor tidak ditemukan.</p>
            </div>
        @endforelse
    </div>

    {{-- PAGINATION LINKS --}}
    <div class="mt-8">
        {{ $motors->links() }}
    </div>
</div>