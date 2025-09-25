<?php

use Livewire\Volt\Component;
use App\Models\Penyewaan;
use App\Models\Motors;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use Toast;

    public $booking;
    public $motor;
    public $payment;
    public $pemilik;
    
    public function mount($id)
    {
        // Ambil detail booking berdasarkan ID dan pastikan milik user yang login
        $this->booking = Penyewaan::with(['motor.owner', 'motor.tarif', 'pembayaran'])
            ->where('ID_Penyewaan', $id)
            ->where('penyewa_id', Auth::id())
            ->firstOrFail();
            
        $this->motor = $this->booking->motor;
        $this->payment = $this->booking->pembayaran;
        $this->pemilik = $this->motor->owner ?? null;
    }

    public function getStatusBadgeClass($status)
    {
        return match($status) {
            'pending' => 'badge badge-warning',
            'dibayar' => 'badge badge-info',
            'disewa' => 'badge badge-primary', 
            'dikembalikan' => 'badge badge-warning',
            'selesai' => 'badge badge-success',
            'dibatalkan' => 'badge badge-error',
            'paid' => 'badge badge-success',
            'failed' => 'badge badge-error',
            default => 'badge badge-neutral'
        };
    }
}; ?>

<div>
    <x-header title="Detail Booking #{{ $booking->ID_Penyewaan }}" separator progress-indicator>
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <x-button icon="o-arrow-left" label="Kembali" link="/bookings" class="btn-outline" />
                
                {{-- Tombol Cancel untuk booking pending --}}
                @if($booking->status === 'pending')
                    <x-button 
                        label="Batalkan" 
                        icon="o-x-mark" 
                        class="btn-error" 
                        @click="$dispatch('showCancelModal', {{ $booking->ID_Penyewaan }})"
                    />
                @endif

                {{-- Tombol Return untuk booking yang sudah dibayar/disewa --}}
                @if(in_array($booking->status, ['dibayar', 'disewa']))
                    <x-button 
                        label="Kembalikan" 
                        icon="o-arrow-uturn-left" 
                        class="btn-warning" 
                        @click="$dispatch('showReturnModal', {{ $booking->ID_Penyewaan }})"
                    />
                @endif

                {{-- Status Badge --}}
                <x-badge value="{{ ucfirst($booking->status) }}" class="{{ $this->getStatusBadgeClass($booking->status) }}" />
            </div>
        </x-slot:actions>
    </x-header>

    <div class="p-4 space-y-6">
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            
            <!-- Informasi Booking -->
            <x-card title="Detail Penyewaan" class="h-fit">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-icon name="o-hashtag" class="w-4 h-4 inline mr-2" />
                            <span class="font-medium">Kode Booking:</span>
                            <div class="mt-1 px-2 py-1 rounded text-mono text-sm bg-gray-100">
                                #{{ $booking->ID_Penyewaan }}
                            </div>
                        </div>
                        
                        <div>
                            <x-icon name="o-flag" class="w-4 h-4 inline mr-2" />
                            <span class="font-medium">Status:</span>
                            <div class="mt-1">
                                <x-badge value="{{ ucfirst($booking->status) }}" class="{{ $this->getStatusBadgeClass($booking->status) }}" />
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <h4 class="font-semibold text-sm mb-3 flex items-center">
                            <x-icon name="o-truck" class="w-4 h-4 mr-2" />
                            Motor yang Disewa
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-gray-600">Merk:</span>
                                <div class="font-medium">{{ $motor->merk }}</div>
                            </div>
                            <div>
                                <span class="text-gray-600">Tipe/CC:</span>
                                <div class="font-medium">{{ $motor->tipe_cc }}</div>
                            </div>
                            <div>
                                <span class="text-gray-600">No. Plat:</span>
                                <div class="font-medium">{{ $motor->no_plat }}</div>
                            </div>
                            <div>
                                <span class="text-gray-600">Pemilik:</span>
                                <div class="font-medium">{{ $pemilik->nama ?? '-' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <h4 class="font-semibold text-sm mb-3 flex items-center">
                            <x-icon name="o-calendar" class="w-4 h-4 mr-2" />
                            Detail Sewa
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-gray-600">Tanggal Mulai:</span>
                                <div class="font-medium">{{ \Carbon\Carbon::parse($booking->tanggal_mulai)->format('d M Y') }}</div>
                            </div>
                            <div>
                                <span class="text-gray-600">Tanggal Selesai:</span>
                                <div class="font-medium">{{ \Carbon\Carbon::parse($booking->tanggal_selesai)->format('d M Y') }}</div>
                            </div>
                            <div>
                                <span class="text-gray-600">Tipe Durasi:</span>
                                <div class="font-medium">{{ ucfirst($booking->tipe_durasi) }}</div>
                            </div>
                            <div>
                                <span class="text-gray-600">Total Harga:</span>
                                <div class="font-bold text-green-600">Rp {{ number_format($booking->harga, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    </div>

                    @if($booking->catatan_pengembalian)
                        <div class="border-t pt-4">
                            <h4 class="font-semibold text-sm mb-2 flex items-center">
                                <x-icon name="o-document-text" class="w-4 h-4 mr-2" />
                                Catatan Pengembalian
                            </h4>
                            <div class="text-sm p-3 bg-yellow-50 rounded border">
                                {{ $booking->catatan_pengembalian }}
                            </div>
                        </div>
                    @endif
                </div>
            </x-card>

            <!-- Informasi Pembayaran -->
            <x-card title="Informasi Pembayaran" class="h-fit">
                @if($payment)
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <x-icon name="o-credit-card" class="w-4 h-4 inline mr-2" />
                                <span class="font-medium">Kode Pembayaran:</span>
                                <div class="mt-1 px-2 py-1 rounded text-mono text-sm bg-gray-100">
                                    {{ $payment->kode_pembayaran }}
                                </div>
                            </div>
                            
                            <div>
                                <x-icon name="o-flag" class="w-4 h-4 inline mr-2" />
                                <span class="font-medium">Status Pembayaran:</span>
                                <div class="mt-1">
                                    <x-badge value="{{ ucfirst($payment->status) }}" class="{{ $this->getStatusBadgeClass($payment->status) }}" />
                                </div>
                            </div>
                        </div>

                        <div class="border-t pt-4">
                            <h4 class="font-semibold text-sm mb-3 flex items-center">
                                <x-icon name="o-banknotes" class="w-4 h-4 mr-2" />
                                Detail Pembayaran
                            </h4>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                <div>
                                    <span class="text-gray-600">Metode Pembayaran:</span>
                                    <div class="font-medium">{{ strtoupper(str_replace('_', ' ', $payment->metode_pembayaran)) }}</div>
                                </div>
                                <div>
                                    <span class="text-gray-600">Jumlah Bayar:</span>
                                    <div class="font-medium text-green-600">Rp {{ number_format($payment->jumlah_bayar, 0, ',', '.') }}</div>
                                </div>
                                @if($payment->uang_bayar)
                                <div>
                                    <span class="text-gray-600">Uang Dibayar:</span>
                                    <div class="font-medium text-blue-600">Rp {{ number_format($payment->uang_bayar, 0, ',', '.') }}</div>
                                </div>
                                @endif
                                @if($payment->uang_kembalian)
                                <div>
                                    <span class="text-gray-600">Uang Kembalian:</span>
                                    <div class="font-medium text-orange-600">Rp {{ number_format($payment->uang_kembalian, 0, ',', '.') }}</div>
                                </div>
                                @endif
                            </div>
                        </div>

                        @if($payment->tanggal_bayar)
                            <div class="border-t pt-4">
                                <h4 class="font-semibold text-sm mb-2 flex items-center">
                                    <x-icon name="o-clock" class="w-4 h-4 mr-2" />
                                    Waktu Pembayaran
                                </h4>
                                <div class="text-sm">
                                    <div class="font-medium">{{ \Carbon\Carbon::parse($payment->tanggal_bayar)->format('d M Y, H:i') }} WIB</div>
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-icon name="o-exclamation-triangle" class="w-16 h-16 mx-auto text-yellow-500 mb-4" />
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum Ada Pembayaran</h3>
                        <p class="text-gray-600">Anda belum melakukan pembayaran untuk booking ini.</p>
                        @if($booking->status === 'pending')
                            <x-button 
                                label="Bayar Sekarang" 
                                icon="o-credit-card" 
                                link="/payments/{{ $booking->ID_Penyewaan }}" 
                                class="btn-primary mt-4" 
                            />
                        @endif
                    </div>
                @endif
            </x-card>
        </div>
    </div>

    {{-- Modal Components --}}
    <livewire:penyewa.motors.cancel />
    <livewire:penyewa.motors.return />
</div>
