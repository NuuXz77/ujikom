<?php

use Livewire\Volt\Component;
use App\Models\Penyewaan;
use App\Models\Motors;
use App\Models\Pembayaran;
use App\Models\BagiHasil;
use App\Models\Transaksi;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\DB;

new class extends Component {
    use Toast;

    public ?int $bookingId = null;
    public ?array $bookingData = null; // ringkasan booking
    public string $selectedMethod = '';
    public ?string $expandedCode = null; // hanya simpan code group yang sedang expand
    
    // Payment form fields
    public float $uangBayar = 0;
    public float $uangKembalian = 0;
    public string $catatan = '';

    // Kelompok metode pembayaran & opsi detailnya
    public array $paymentGroups = [
        [
            'code' => 'ebank',
            'label' => 'E-Banking',
            'logo' => null, // logo dihilangkan demi performa
            'methods' => [['value' => 'bca_va', 'label' => 'BCA Virtual Account', 'logo' => '/images/payments/bca.png'], ['value' => 'bri_va', 'label' => 'BRI Virtual Account', 'logo' => '/images/payments/bri.png'], ['value' => 'mandiri_va', 'label' => 'Mandiri VA', 'logo' => '/images/payments/mandiri.png']],
        ],
        [
            'code' => 'ewallet',
            'label' => 'E-Wallet',
            'logo' => null,
            'methods' => [['value' => 'ovo', 'label' => 'OVO', 'logo' => '/images/payments/ovo.png'], ['value' => 'gopay', 'label' => 'GoPay', 'logo' => '/images/payments/gopay.png'], ['value' => 'dana', 'label' => 'DANA', 'logo' => '/images/payments/dana.png']],
        ],
        [
            'code' => 'qris',
            'label' => 'QRIS',
            'logo' => null,
            'methods' => [['value' => 'qris_static', 'label' => 'QRIS Static', 'logo' => '/images/payments/qris.png']],
        ],
        [
            'code' => 'cash',
            'label' => 'Tunai',
            'logo' => null,
            'methods' => [['value' => 'cash', 'label' => 'Pembayaran Tunai', 'logo' => null]],
        ],
    ];

    protected $rules = [
        'selectedMethod' => 'required',
        'uangBayar' => 'required|numeric|min:0',
    ];

    public function mount(int $id): void
    {
        $this->bookingId = $id;
        $this->loadBooking();
    }

    public function loadBooking(): void
    {
        $booking = Penyewaan::with('motor')->where('ID_Penyewaan', $this->bookingId)->where('penyewa_id', Auth::id())->first();
        if (!$booking) {
            $this->bookingData = null;
            return;
        }
        $this->bookingData = [
            'kode' => $booking->ID_Penyewaan,
            'motor' => $booking->motor?->merk,
            'plat' => $booking->motor?->no_plat,
            'mulai' => $booking->tanggal_mulai,
            'selesai' => $booking->tanggal_selesai,
            'durasi' => $booking->tipe_durasi,
            'harga' => $booking->harga,
            'status' => $booking->status,
            'motor_id' => $booking->motor_id,
        ];
        
        // Set default uang bayar sama dengan harga
        $this->uangBayar = $this->bookingData['harga'];
        $this->calculateKembalian();
    }

    public function selectMethod($value)
    {
        $this->selectedMethod = $value;
    }

    public function toggleGroup($code)
    {
        $this->expandedCode = $this->expandedCode === $code ? null : $code;
    }

    public function updatedUangBayar()
    {
        $this->calculateKembalian();
    }

    public function calculateKembalian()
    {
        if ($this->bookingData && $this->uangBayar > 0) {
            $this->uangKembalian = max(0, $this->uangBayar - $this->bookingData['harga']);
        } else {
            $this->uangKembalian = 0;
        }
    }

    public function confirmPayment()
    {
        $this->validate();

        if ($this->uangBayar < $this->bookingData['harga']) {
            $this->toast('error', 'Error!', 'Uang bayar tidak boleh kurang dari total harga');
            return;
        }

        try {
            DB::beginTransaction();

            // Create pembayaran record
            $pembayaran = Pembayaran::create([
                'penyewaan_id' => $this->bookingId,
                'metode_pembayaran' => $this->selectedMethod,
                'jumlah_bayar' => $this->bookingData['harga'],
                'uang_bayar' => $this->uangBayar,
                'uang_kembalian' => $this->uangKembalian,
                'status' => 'paid',
                'kode_pembayaran' => Pembayaran::generateKodePembayaran(),
                'catatan' => $this->catatan,
                'tanggal_bayar' => now(),
            ]);

            // Create transaksi record
            Transaksi::create([
                'penyewaan_id' => $this->bookingId,
                'metode_pembayaran' => $this->selectedMethod,
                'status' => 'completed',
                'tanggal' => now(),
                'jumlah' => $this->bookingData['harga'],
            ]);

            // Update penyewaan status
            $penyewaan = Penyewaan::find($this->bookingId);
            $penyewaan->status = 'dibayar';
            $penyewaan->save();

            // Update status motor menjadi 'disewa'
            $motor = Motors::find($this->bookingData['motor_id']);
            if ($motor) {
                $motor->status = 'disewa';
                $motor->save();
            }

            // Hitung dan buat record bagi hasil
            $totalHarga = $this->bookingData['harga'];
            $bagiHasilAdmin = $totalHarga * 0.70; // 70% untuk admin
            $bagiHasilPemilik = $totalHarga * 0.30; // 30% untuk pemilik

            // Create bagi hasil record
            BagiHasil::create([
                'penyewaan_id' => $this->bookingId,
                'bagi_hasil_pemilik' => $bagiHasilPemilik,
                'bagi_hasil_admin' => $bagiHasilAdmin,
                'settled_at' => now(),
            ]);

            DB::commit();

            $this->toast('success', 'Sukses!', 'Pembayaran berhasil dikonfirmasi. Transaksi dan bagi hasil telah diproses.');
            return redirect('/dashboard');
        } catch (\Exception $e) {
            DB::rollback();
            $this->toast('error', 'Error!', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }
}; ?>

<div class="space-y-8">
    <x-header title="Pembayaran" subtitle="Konfirmasi pembayaran booking" separator />

    <div class="grid gap-6 md:grid-cols-2">
        <div class="space-y-6">
            <x-card title="Informasi Booking" shadow>
                <x-slot:menu>
                    <x-icon name="o-clipboard-document-list" class="w-5 h-5" />
                </x-slot:menu>
                @if ($bookingData)
                    <div class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        <div class="text-gray-500 flex items-center gap-2">
                            <x-icon name="o-hashtag" class="w-4 h-4" />
                            Kode Booking
                        </div>
                        <div class="font-medium">#{{ $bookingData['kode'] }}</div>
                        
                        <div class="text-gray-500 flex items-center gap-2">
                            <x-icon name="o-truck" class="w-4 h-4" />
                            Motor
                        </div>
                        <div class="font-medium">{{ $bookingData['motor'] }}</div>
                        
                        <div class="text-gray-500 flex items-center gap-2">
                            <x-icon name="o-identification" class="w-4 h-4" />
                            Plat
                        </div>
                        <div class="font-medium">{{ $bookingData['plat'] }}</div>
                        
                        <div class="text-gray-500 flex items-center gap-2">
                            <x-icon name="o-calendar" class="w-4 h-4" />
                            Tanggal Mulai
                        </div>
                        <div>{{ $bookingData['mulai'] }}</div>
                        
                        <div class="text-gray-500 flex items-center gap-2">
                            <x-icon name="o-calendar" class="w-4 h-4" />
                            Tanggal Selesai
                        </div>
                        <div>{{ $bookingData['selesai'] }}</div>
                        
                        <div class="text-gray-500 flex items-center gap-2">
                            <x-icon name="o-clock" class="w-4 h-4" />
                            Durasi
                        </div>
                        <div>{{ ucfirst($bookingData['durasi']) }}</div>
                        
                        <div class="text-gray-500 flex items-center gap-2">
                            <x-icon name="o-signal" class="w-4 h-4" />
                            Status
                        </div>
                        <div>
                            <span
                                class="px-2 py-0.5 rounded text-xs {{ $bookingData['status'] === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">{{ strtoupper($bookingData['status']) }}</span>
                        </div>
                        
                        <div class="col-span-2 h-px bg-base-200 my-1"></div>
                        
                        <div class="text-gray-500 flex items-center gap-2">
                            <x-icon name="o-currency-dollar" class="w-4 h-4" />
                            Total Bayar
                        </div>
                        <div class="text-lg font-bold text-primary">Rp
                            {{ number_format($bookingData['harga'], 0, ',', '.') }}</div>
                    </div>
                @else
                    <p class="text-sm text-red-500">Booking tidak ditemukan atau tidak milik Anda.</p>
                @endif
            </x-card>
        </div>
        
        <div class="space-y-6">
            <x-card title="Pilih Metode Pembayaran" shadow>
                <x-slot:menu>
                    <x-icon name="o-credit-card" class="w-5 h-5" />
                </x-slot:menu>
                <div class="space-y-4">
                    <div class="space-y-2">
                        @foreach ($paymentGroups as $group)
                            <div class="rounded-md overflow-hidden bg-base-100">
                                <button type="button" wire:click="toggleGroup('{{ $group['code'] }}')"
                                    class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-base-200">
                                    <div class="flex items-center gap-3">
                                        <span class="font-medium">{{ $group['label'] }}</span>
                                    </div>
                                    <x-icon name="o-chevron-down"
                                        class="h-5 w-5 transition-transform {{ $expandedCode === $group['code'] ? 'rotate-180' : '' }}" />
                                </button>
                                <div
                                    class="transition-all duration-300 {{ $expandedCode === $group['code'] ? 'max-h-[800px] opacity-100' : 'max-h-0 opacity-0 pointer-events-none' }} overflow-hidden">
                                    <div class="p-4 grid md:grid-cols-3 gap-4">
                                        @foreach ($group['methods'] as $m)
                                            <label class="flex items-center gap-3 p-3 rounded cursor-pointer bg-base-50 hover:bg-base-100 {{ $selectedMethod === $m['value'] ? 'border-primary ring-1 ring-primary' : 'border-gray-200' }}">
                                                <input type="radio" class="hidden" wire:click="selectMethod('{{ $m['value'] }}')">
                                                <span class="text-sm font-medium">{{ $m['label'] }}</span>
                                                @if ($selectedMethod === $m['value'])
                                                    <x-badge value="Dipilih" class="badge-primary badge-soft ml-auto" />
                                                @endif
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('selectedMethod')
                        <p class="text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>
            </x-card>

            <!-- Payment Amount Form -->
            <x-card title="Konfirmasi Pembayaran" shadow>
                <x-slot:menu>
                    <x-icon name="o-banknotes" class="w-5 h-5" />
                </x-slot:menu>
                <div class="space-y-4">
                    <!-- Total yang harus dibayar -->
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600 flex items-center gap-2">
                                <x-icon name="o-calculator" class="w-4 h-4" />
                                Total yang harus dibayar:
                            </span>
                            <span class="text-lg font-bold text-blue-600">
                                Rp {{ $bookingData ? number_format($bookingData['harga'], 0, ',', '.') : '0' }}
                            </span>
                        </div>
                    </div>

                    <!-- Input uang bayar -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 flex items-center gap-2">
                            <x-icon name="o-banknotes" class="w-4 h-4" />
                            Uang Bayar
                        </label>
                        <x-input 
                            wire:model.live="uangBayar" 
                            type="number" 
                            min="0" 
                            step="1000"
                            placeholder="Masukkan jumlah uang bayar"
                            prefix="Rp"
                            class="text-right"
                            error-field="uangBayar"
                            icon="o-currency-dollar"
                        />
                        @error('uangBayar')
                            <p class="text-xs text-red-500">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Uang kembalian (auto calculate) -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 flex items-center gap-2">
                            <x-icon name="o-arrow-uturn-left" class="w-4 h-4" />
                            Uang Kembalian
                        </label>
                        <div class="relative">
                            <div class="flex">
                                <span class="inline-flex items-center px-3 rounded-l-md border border-r-0 border-gray-300 bg-gray-50 text-gray-500 text-sm">
                                    Rp
                                </span>
                                <input 
                                    type="text" 
                                    value="{{ number_format($uangKembalian, 0, ',', '.') }}"
                                    readonly 
                                    class="flex-1 block w-full rounded-none rounded-r-md border-gray-300 bg-gray-50 text-right {{ $uangKembalian > 0 ? 'text-green-600 font-semibold' : 'text-gray-500' }}"
                                >
                            </div>
                        </div>
                        @if($uangKembalian > 0)
                            <p class="text-xs text-green-600">✓ Kembalian: Rp {{ number_format($uangKembalian, 0, ',', '.') }}</p>
                        @endif
                        @if($bookingData && $uangBayar > 0 && $uangBayar < $bookingData['harga'])
                            <p class="text-xs text-red-500">⚠️ Uang bayar kurang dari total harga</p>
                        @endif
                    </div>

                    <!-- Catatan opsional -->
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700 flex items-center gap-2">
                            <x-icon name="o-chat-bubble-left-ellipsis" class="w-4 h-4" />
                            Catatan (Opsional)
                        </label>
                        <x-textarea 
                            wire:model="catatan" 
                            placeholder="Tambahkan catatan pembayaran..."
                            rows="3"
                        />
                    </div>

                    @if (session('payment_success'))
                        <div class="p-3 rounded bg-green-50 text-green-700 text-sm">{{ session('payment_success') }}
                        </div>
                    @endif
                </div>
            </x-card>

            <div class="flex justify-end">
                <x-button 
                    class="btn-primary btn-wide" 
                    icon="o-check-circle" 
                    :disabled="!$selectedMethod || !$bookingData || $uangBayar < ($bookingData['harga'] ?? 0)" 
                    wire:click="confirmPayment"
                    spinner="confirmPayment"
                >
                    Konfirmasi Pembayaran
                </x-button>
            </div>
        </div>
    </div>
</div>
