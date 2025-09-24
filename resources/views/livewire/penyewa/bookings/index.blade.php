<?php

use Livewire\Volt\Component;
use App\Models\Motors;
use App\Models\TarifRental;
use App\Models\Penyewaan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

new class extends Component {
    public ?int $motorId = null;            // Diisi dari parameter route /bookings/{id}
    public string $tipe_durasi = 'daily';   // daily | weekly | monthly
    public string $tanggal_mulai = '';      // format Y-m-d
    public string $tanggal_selesai = '';    // dihitung otomatis
    public float $harga = 0;                // total harga terhitung
    public int $jumlah = 1;                 // jumlah periode (hari / minggu / bulan)
    public bool $bookingCreated = false;    // menandai sudah tersimpan
    public ?int $bookingId = null;          // ID_Penyewaan hasil create
    public array $durasiOptions = [
        ['value' => 'daily', 'label' => 'Harian'],
        ['value' => 'weekly', 'label' => 'Mingguan'],
        ['value' => 'monthly', 'label' => 'Bulanan'],
    ];
    public ?array $motorData = null;        // ringkas data motor + tarif

    protected $rules = [
        // motorId sudah dipastikan lewat parameter route
        'tipe_durasi' => 'required|in:daily,weekly,monthly',
        'tanggal_mulai' => 'required|date|after_or_equal:today',
        'jumlah' => 'required|integer|min:1|max:365',
    ];

    public function mount(int $id)
    {
        $this->motorId = $id;
        $this->tanggal_mulai = now()->format('Y-m-d');
        $this->recalc();
    }

    public function updatedTipeDurasi(): void { $this->recalc(); }
    public function updatedTanggalMulai(): void { $this->recalc(); }
    public function updatedJumlah(): void { $this->recalc(); }

    public function recalc(): void
    {
        if (!$this->motorId) { return; }
        $motor = Motors::with('tarif')->find($this->motorId);
        if (!$motor || !$motor->tarif) { $this->harga = 0; return; }

        $start = Carbon::parse($this->tanggal_mulai ?: now());
        $qty = max(1, (int)$this->jumlah);
        switch ($this->tipe_durasi) {
            case 'weekly':
                $end = $start->copy()->addWeeks($qty);
                $rate = $motor->tarif->tarif_mingguan ?? 0; break;
            case 'monthly':
                $end = $start->copy()->addMonths($qty);
                $rate = $motor->tarif->tarif_bulanan ?? 0; break;
            default:
                $end = $start->copy()->addDays($qty);
                $rate = $motor->tarif->tarif_harian ?? 0; break;
        }
        $this->tanggal_selesai = $end->format('Y-m-d');
        $this->harga = (float) ($rate * $qty);
        $this->motorData = [
            'merk' => $motor->merk,
            'no_plat' => $motor->no_plat,
            'tipe_cc' => $motor->tipe_cc,
            'foto' => $motor->photo ?? null,
            'status' => $motor->status,
        ];
    }

    public function createBooking(): void
    {
    // motorId berasal dari route, cukup validasi field lain
    $this->validate();
    if (!$this->motorId) { return; }
        $motor = Motors::with('tarif')->find($this->motorId);
        if (!$motor) { $this->addError('motorId', 'Motor tidak ditemukan'); return; }
        // Cegah booking ganda jika motor sudah dibooking (pending) atau disewa
        if (!in_array($motor->status, ['tersedia'])) {
            $this->addError('motorId', 'Motor sedang tidak tersedia (status: '. $motor->status .').'); return; }

        // Hitung ulang untuk konsistensi
        $this->recalc();

        $booking = Penyewaan::create([
            'penyewa_id' => Auth::id(),
            'motor_id' => $this->motorId,
            'tanggal_mulai' => $this->tanggal_mulai,
            'tanggal_selesai' => $this->tanggal_selesai,
            'tipe_durasi' => $this->tipe_durasi,
            'status' => 'pending',
            'harga' => $this->harga,
        ]);

        // Set status motor menjadi dibooking agar tidak bisa dipakai user lain sampai dibayar / dibatalkan
        $motor->update(['status' => 'dibooking']);

        $this->bookingCreated = true;
        $this->bookingId = $booking->ID_Penyewaan;

        // (Optional) ubah status motor menjadi 'disewa' sementara atau 'sedang_diverifikasi' sesuai flow bisnis
        // $motor->update(['status' => 'disewa']);
    }

    public function goToPayment(): void
    {
        if($this->bookingId){
            // Redirect ke halaman payments dengan id booking
            $this->redirect(route('payments-penyewa', ['id' => $this->bookingId]));
        }
    }
}; ?>

<div class="space-y-8">
    <x-header title="Booking Motor" subtitle="Pilih durasi dan lanjutkan pembayaran" separator />

    {{-- FORM PEMILIHAN DURASI --}}
    <div class="grid gap-6 md:grid-cols-3">
        <div class="md:col-span-2 space-y-6">
            <x-card title="Detail Booking" shadow>
                <div class="space-y-4">
                    @if(!$motorId)
                        <div class="p-4 rounded border border-amber-300 bg-amber-50 text-sm text-amber-700">
                            ID Motor tidak ditemukan. Akses halaman ini melalui tautan seperti /bookings/{ID_Motor}.
                        </div>
                    @endif
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <x-input type="date" label="Tanggal Mulai" wire:model.live="tanggal_mulai" />
                            @error('tanggal_mulai') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex-1">
                            <x-input type="date" label="Tanggal Selesai" disabled :value="$tanggal_selesai" />
                        </div>
                    </div>
                    <div class="space-y-2">
                        <p class="text-sm font-medium">Tipe Durasi</p>
                        <div class="flex flex-wrap gap-3">
                            @foreach($durasiOptions as $opt)
                                <button type="button" wire:click="set('tipe_durasi','{{ $opt['value'] }}')"
                                    class="px-4 py-2 rounded border text-sm {{ $tipe_durasi === $opt['value'] ? 'bg-primary text-white border-primary' : 'border-gray-300 hover:bg-gray-100' }}">
                                    {{ $opt['label'] }}
                                </button>
                            @endforeach
                        </div>
                        @error('tipe_durasi') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="flex flex-col md:flex-row gap-4 items-end">
                        <div class="w-full md:w-40">
                            <x-input type="number" min="1" max="365" label="Jumlah" wire:model.live="jumlah" placeholder="1" />
                            @error('jumlah') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="flex-1 text-sm text-gray-500">
                            @if($tipe_durasi==='daily') <x-badge value="Jumlah hari yang ingin disewa." class="badge-primary badge-soft"/>
                            @elseif($tipe_durasi==='weekly') <span>Jumlah minggu yang ingin disewa.</span>
                            @else <span>Jumlah bulan yang ingin disewa.</span>@endif
                        </div>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Harga = Tarif {{ $tipe_durasi==='daily' ? 'harian' : ($tipe_durasi==='weekly' ? 'mingguan' : 'bulanan') }} x {{ $jumlah }}</p>
                        <p class="text-2xl font-bold mt-1">Rp {{ number_format($harga, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <x-button wire:click="createBooking" :disabled="!$motorId || $harga <=0" icon="o-credit-card" class="btn-primary">Lanjutkan Pembayaran</x-button>
                    </div>
                </div>
            </x-card>

            @if($bookingCreated)
                <x-card title="Pembayaran" shadow>
                    <div class="space-y-4">
                        <p class="text-sm text-gray-600">Booking berhasil dibuat dengan status <span class="font-semibold">pending</span>. Silakan selesaikan pembayaran.</p>
                        <div class="grid gap-2 text-sm">
                            <div class="flex justify-between"><span>Kode Booking</span><span>#{{ $bookingId }}</span></div>
                            <div class="flex justify-between"><span>Tanggal Mulai</span><span>{{ $tanggal_mulai }}</span></div>
                            <div class="flex justify-between"><span>Tanggal Selesai</span><span>{{ $tanggal_selesai }}</span></div>
                            <div class="flex justify-between"><span>Durasi</span><span>{{ ucfirst($tipe_durasi) }}</span></div>
                            <div class="flex justify-between font-semibold"><span>Total</span><span>Rp {{ number_format($harga,0,',','.') }}</span></div>
                        </div>
                        <div class="flex gap-3">
                            <x-button icon="o-check-circle" class="btn-success" wire:click="goToPayment">Bayar Sekarang</x-button>
                            <x-button icon="o-arrow-path" class="btn-ghost" wire:click="$refresh">Refresh</x-button>
                        </div>
                    </div>
                </x-card>
            @endif
        </div>

        <div class="space-y-6">
            <x-card title="Info Motor" shadow>
                @if($motorData)
                    <div class="space-y-2 text-sm">
                        <p><span class="font-medium">Merk:</span> {{ $motorData['merk'] }}</p>
                        <p><span class="font-medium">Plat:</span> {{ $motorData['no_plat'] }}</p>
                        <p><span class="font-medium">CC:</span> {{ $motorData['tipe_cc'] }}</p>
                        <p><span class="font-medium">Status:</span>
                            @if($motorData['status']==='tersedia') <span class="text-green-600">Tersedia</span>
                            @elseif($motorData['status']==='disewa') <span class="text-orange-600">Disewa</span>
                            @elseif($motorData['status']==='sedang_diverifikasi') <span class="text-yellow-600">Verifikasi</span>
                            @elseif($motorData['status']==='dibooking') <span class="text-amber-600">Dibooking</span>
                            @else <span class="text-red-600">Perawatan</span> @endif
                        </p>
                    </div>
                @else
                    <p class="text-sm text-gray-500">Masukkan ID Motor untuk melihat info.</p>
                @endif
            </x-card>
        </div>
    </div>
</div>
