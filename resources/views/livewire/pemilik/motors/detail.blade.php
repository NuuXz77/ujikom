<?php

use Livewire\Volt\Component;
use App\Models\Motors;
use App\Models\TarifRental;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use Toast;

    public $motor;
    public $owner;
    public $tarif_harian;
    public $tarif_mingguan;
    public $tarif_bulanan;
    public $pendapatan_total = 0;

    public function mount($id)
    {
        // Pastikan motor milik user yang sedang login
        $this->motor = Motors::with(['tarif', 'owner'])
            ->where('ID_Motor', $id)
            ->where('owner_id', Auth::id())
            ->firstOrFail();
            
        $this->owner = $this->motor->owner;
        $this->tarif_harian = $this->motor->tarif->tarif_harian ?? 0;
        $this->tarif_mingguan = $this->motor->tarif->tarif_mingguan ?? 0;
        $this->tarif_bulanan = $this->motor->tarif->tarif_bulanan ?? 0;

        // Hitung total pendapatan dari bagi hasil
        $this->pendapatan_total = \DB::table('penyewaans')
            ->join('bagi_hasils', 'bagi_hasils.penyewaan_id', '=', 'penyewaans.ID_Penyewaan')
            ->where('penyewaans.motor_id', $this->motor->ID_Motor)
            ->sum('bagi_hasils.bagi_hasil_pemilik');
    }
}; ?>

<div>
    <x-header title="Detail Motor {{ $motor->merk }}" separator progress-indicator>
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <x-button icon="o-arrow-left" label="Kembali" link="/owner/motors" class="btn-outline" />
                <x-button icon="o-pencil" label="Edit" link="/owner/motors/edit/{{ $motor->ID_Motor }}" class="btn-primary" />
                
                @if ($motor->status == 'tersedia')
                    <x-badge value="Tersedia" class="badge badge-success" />
                @elseif ($motor->status == 'disewa')
                    <x-badge value="Disewa" class="badge badge-primary" />
                @elseif ($motor->status == 'sedang_diverifikasi')
                    <x-badge value="Sedang Diverifikasi" class="badge badge-warning" />
                @elseif ($motor->status == 'perawatan')
                    <x-badge value="Perawatan" class="badge badge-error" />
                @elseif ($motor->status == 'dibayar')
                    <x-badge value="Dibayar" class="badge badge-info" />
                @else
                    <x-badge value="-" class="badge badge-neutral" />
                @endif
            </div>
        </x-slot:actions>
    </x-header>

    <div class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            
            {{-- Informasi Motor --}}
            <x-card title="Informasi Motor" class="h-fit">
                <div class="space-y-4">
                    <x-input label="Merk" value="{{ $motor->merk }}" readonly icon="o-tag" />
                    <x-input label="Tipe/CC" value="{{ $motor->tipe_cc }}" readonly icon="o-cog-6-tooth" />
                    <x-input label="Nomor Polisi" value="{{ $motor->no_plat }}" readonly icon="o-identification" />
                    <x-input label="Status" value="{{ ucfirst($motor->status) }}" readonly icon="o-signal" />
                    <x-input label="Total Pendapatan" value="Rp {{ number_format($pendapatan_total, 0, ',', '.') }}" readonly icon="o-banknotes" class="text-green-600 font-bold" />
                </div>
            </x-card>

            {{-- Informasi Tarif --}}
            <x-card title="Tarif Rental" class="h-fit">
                <div class="space-y-4">
                    <x-input 
                        label="Tarif Harian" 
                        value="Rp {{ number_format($tarif_harian, 0, ',', '.') }}" 
                        readonly 
                        icon="o-calendar-days" 
                    />
                    <x-input 
                        label="Tarif Mingguan" 
                        value="Rp {{ number_format($tarif_mingguan, 0, ',', '.') }}" 
                        readonly 
                        icon="o-calendar" 
                    />
                    <x-input 
                        label="Tarif Bulanan" 
                        value="Rp {{ number_format($tarif_bulanan, 0, ',', '.') }}" 
                        readonly 
                        icon="o-calendar-days" 
                    />
                </div>
            </x-card>

            {{-- Statistik Motor --}}
            <x-card title="Statistik Motor" class="h-fit">
                <div class="grid grid-cols-2 gap-4">
                    @php
                        $stats = \DB::table('penyewaans')
                            ->where('motor_id', $motor->ID_Motor)
                            ->selectRaw('
                                COUNT(*) as total_booking,
                                COUNT(CASE WHEN status = "selesai" THEN 1 END) as selesai,
                                COUNT(CASE WHEN status = "disewa" THEN 1 END) as aktif,
                                COUNT(CASE WHEN status = "dibatalkan" THEN 1 END) as dibatalkan
                            ')
                            ->first();
                    @endphp
                    
                    <div class="text-center p-4 bg-blue-50 rounded-lg">
                        <div class="text-2xl font-bold text-blue-600">{{ $stats->total_booking ?? 0 }}</div>
                        <div class="text-sm text-blue-800">Total Booking</div>
                    </div>
                    
                    <div class="text-center p-4 bg-green-50 rounded-lg">
                        <div class="text-2xl font-bold text-green-600">{{ $stats->selesai ?? 0 }}</div>
                        <div class="text-sm text-green-800">Selesai</div>
                    </div>
                    
                    <div class="text-center p-4 bg-yellow-50 rounded-lg">
                        <div class="text-2xl font-bold text-yellow-600">{{ $stats->aktif ?? 0 }}</div>
                        <div class="text-sm text-yellow-800">Aktif</div>
                    </div>
                    
                    <div class="text-center p-4 bg-red-50 rounded-lg">
                        <div class="text-2xl font-bold text-red-600">{{ $stats->dibatalkan ?? 0 }}</div>
                        <div class="text-sm text-red-800">Dibatalkan</div>
                    </div>
                </div>
            </x-card>

            {{-- Riwayat Penyewaan Terbaru --}}
            <x-card title="Riwayat Penyewaan Terbaru" class="h-fit">
                @php
                    $recent_rentals = \DB::table('penyewaans')
                        ->join('users', 'users.id', '=', 'penyewaans.penyewa_id')
                        ->where('penyewaans.motor_id', $motor->ID_Motor)
                        ->select('penyewaans.*', 'users.nama as penyewa_nama')
                        ->orderBy('penyewaans.created_at', 'desc')
                        ->limit(5)
                        ->get();
                @endphp
                
                @if($recent_rentals->count() > 0)
                    <div class="space-y-3">
                        @foreach($recent_rentals as $rental)
                            <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                                <div>
                                    <div class="font-medium">{{ $rental->penyewa_nama }}</div>
                                    <div class="text-sm text-gray-600">
                                        {{ \Carbon\Carbon::parse($rental->tanggal_mulai)->format('d M Y') }} - 
                                        {{ \Carbon\Carbon::parse($rental->tanggal_selesai)->format('d M Y') }}
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-bold text-green-600">Rp {{ number_format($rental->harga, 0, ',', '.') }}</div>
                                    <x-badge value="{{ ucfirst($rental->status) }}" class="badge badge-sm" />
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-icon name="o-document" class="w-16 h-16 mx-auto text-gray-400 mb-4" />
                        <p class="text-gray-600">Belum ada riwayat penyewaan</p>
                    </div>
                @endif
            </x-card>
        </div>
    </div>
</div>
