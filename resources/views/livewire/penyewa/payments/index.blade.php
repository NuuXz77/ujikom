<?php

use Livewire\Volt\Component;
use App\Models\Penyewaan;
use App\Models\Motors;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

new class extends Component {
    public ?int $bookingId = null;
    public ?array $bookingData = null; // ringkasan booking
    public string $selectedMethod = '';
    public ?string $expandedCode = null; // hanya simpan code group yang sedang expand

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
    ];

    protected $rules = [
        'selectedMethod' => 'required',
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
        ];
    }

    public function selectMethod($value): void
    {
        $this->selectedMethod = $value;
    }

    public function toggleGroup($code): void
    {
        $this->expandedCode = $this->expandedCode === $code ? null : $code;
    }

    public function confirmPayment(): void
    {
        $this->validate();
        // TODO: Simpan transaksi / update status booking -> 'dibayar'
        session()->flash('payment_success', 'Metode pembayaran dipilih: ' . $this->selectedMethod);
    }
}; ?>

<div class="space-y-8">
    <x-header title="Pembayaran" subtitle="Konfirmasi pembayaran booking" separator />

    <div class="grid gap-6 md:grid-cols-2">
        <div class="space-y-6">
            <x-card title="Informasi Booking" shadow>
                @if ($bookingData)
                    <div class="grid grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        <div class="text-gray-500">Kode Booking</div>
                        <div class="font-medium">#{{ $bookingData['kode'] }}</div>
                        <div class="text-gray-500">Motor</div>
                        <div class="font-medium">{{ $bookingData['motor'] }}</div>
                        <div class="text-gray-500">Plat</div>
                        <div class="font-medium">{{ $bookingData['plat'] }}</div>
                        <div class="text-gray-500">Tanggal Mulai</div>
                        <div>{{ $bookingData['mulai'] }}</div>
                        <div class="text-gray-500">Tanggal Selesai</div>
                        <div>{{ $bookingData['selesai'] }}</div>
                        <div class="text-gray-500">Durasi</div>
                        <div>{{ ucfirst($bookingData['durasi']) }}</div>
                        <div class="text-gray-500">Status</div>
                        <div>
                            <span
                                class="px-2 py-0.5 rounded text-xs {{ $bookingData['status'] === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' }}">{{ strtoupper($bookingData['status']) }}</span>
                        </div>
                        <div class="col-span-2 h-px bg-base-200 my-1"></div>
                        <div class="text-gray-500">Total Bayar</div>
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

                    @if (session('payment_success'))
                        <div class="p-3 rounded bg-green-50 text-green-700 text-sm">{{ session('payment_success') }}
                        </div>
                    @endif
                </div>
            </x-card>

            <div class="flex justify-end">
                <x-button class="btn-primary" icon="o-check" :disabled="!$selectedMethod || !$bookingData" wire:click="confirmPayment">Konfirmasi
                    Pembayaran</x-button>
            </div>
        </div>
    </div>
</div>
