<?php

use Livewire\Volt\Component;
use App\Models\Penyewaan;
use App\Models\Motors;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public $booking;
    public $payment;
    public $motor;
    public $penyewa;
    
    public function mount($id)
    {
        $this->booking = Penyewaan::with(['motor.owner', 'user', 'pembayaran'])
            ->findOrFail($id);
            
        $this->payment = $this->booking->pembayaran;
        $this->motor = $this->booking->motor;
        $this->penyewa = $this->booking->user;
    }

    // Konfirmasi ubah status motor menjadi 'disewa' ketika pembayaran sudah PAID
    public function confirmRental()
    {
        if (!$this->payment || !$this->payment->isPaid()) {
            $this->error('Tidak dapat konfirmasi. Pembayaran belum berstatus paid.');
            return;
        }

        try {
            // Hanya update status motor menjadi 'disewa'
            if ($this->motor && $this->motor->status !== 'disewa') {
                $this->motor->update(['status' => 'disewa']);
            }

            $this->success('Motor berhasil dikonfirmasi. Status motor sekarang: disewa.');

            // Refresh data tampilan
            $this->mount($this->booking->ID_Penyewaan);
            $this->dispatch('refresh');
        } catch (\Exception $e) {
            $this->error('Gagal konfirmasi: ' . $e->getMessage());
        }
    }

    // Konfirmasi pengembalian motor
    public function confirmReturn()
    {
        if ($this->booking->status !== 'menunggu_verifikasi_pengembalian') {
            $this->error('Tidak dapat konfirmasi pengembalian. Status booking saat ini: ' . $this->booking->status);
            return;
        }

        try {
            // Update status booking menjadi 'selesai'
            $this->booking->update(['status' => 'selesai']);
            
            // Update status motor menjadi 'tersedia'
            if ($this->motor) {
                $this->motor->update(['status' => 'tersedia']);
            }

            $this->success('Pengembalian berhasil dikonfirmasi. Motor sekarang tersedia untuk disewa.');

            // Refresh data tampilan
            $this->mount($this->booking->ID_Penyewaan);
            $this->dispatch('refresh');
        } catch (\Exception $e) {
            $this->error('Gagal konfirmasi pengembalian: ' . $e->getMessage());
        }
    }

    public function activateBooking()
    {
        if ($this->booking->status !== 'pending') {
            $this->error('Booking tidak dapat diaktifkan. Status saat ini: ' . $this->booking->status);
            return;
        }

        if (!$this->payment || !$this->payment->isPaid()) {
            $this->error('Pembayaran belum lunas atau belum ada.');
            return;
        }

        try {
            // Update status booking
            $this->booking->update(['status' => 'active']);
            
            // Update status motor menjadi 'disewa'
            $this->motor->update(['status' => 'disewa']);
            
            $this->success('Booking berhasil diaktifkan dan motor telah berstatus disewa!');
            
            // Refresh data
            $this->mount($this->booking->ID_Penyewaan);
            
            // Dispatch event untuk refresh parent jika ada
            $this->dispatch('refresh');
            
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function completeBooking()
    {
        if ($this->booking->status !== 'active') {
            $this->error('Booking tidak dapat diselesaikan. Status saat ini: ' . $this->booking->status);
            return;
        }

        try {
            // Update status booking
            $this->booking->update(['status' => 'completed']);
            
            // Update status motor menjadi 'tersedia'
            $this->motor->update(['status' => 'tersedia']);
            
            $this->success('Booking berhasil diselesaikan dan motor telah tersedia kembali!');
            
            // Refresh data
            $this->mount($this->booking->ID_Penyewaan);
            
            // Dispatch event untuk refresh parent jika ada
            $this->dispatch('refresh');
            
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function cancelBooking()
    {
        // if (!in_array($this->booking->status, ['pending', 'active'])) {
        //     $this->error('Booking tidak dapat dibatalkan. Status saat ini: ' . $this->booking->status);
        //     return;
        // }

        try {
            // Update status booking
            $this->booking->update(['status' => 'selesai']);
            
            // Jika booking sedang aktif, kembalikan status motor ke tersedia
            if ($this->booking->status === 'selesai') {
                $this->motor->update(['status' => 'tersedia']);
            }
            
            $this->success('Booking berhasil dibatalkan!');
            
            // Refresh data
            $this->mount($this->booking->ID_Penyewaan);
            
            // Dispatch event untuk refresh parent jika ada
            $this->dispatch('refresh');
            
        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function getStatusBadgeClass($status)
    {
        return match($status) {
            'pending' => 'badge badge-warning',
            'active' => 'badge badge-primary', 
            'completed' => 'badge badge-success',
            'canceled' => 'badge badge-error',
            'paid' => 'badge badge-success',
            'failed' => 'badge badge-error',
            'dibayar' => 'badge badge-info',
            'disewa' => 'badge badge-primary',
            'dikembalikan' => 'badge badge-warning',
            'selesai' => 'badge badge-success',
            default => 'badge badge-neutral'
        };
    }
}; ?>

<div>
    <x-header title="Detail Booking #{{ $booking->ID_Penyewaan }}" separator progress-indicator>
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <x-button label="Kembali" icon="o-arrow-left" link="/admin/bookings" class="btn-outline" />
                
                {{-- Hanya tombol konfirmasi ketika booking sudah dibayar dan motor belum disewa --}}
                @if($booking->status === 'dibayar' && $payment && $payment->isPaid() && $motor->status !== 'disewa')
                    <x-button 
                        label="Konfirmasi Penyewaan" 
                        icon="o-check-circle" 
                        class="btn-primary" 
                        wire:click="confirmRental"
                        wire:confirm="Konfirmasi penyewaan ini? Motor akan diubah ke status 'disewa'."
                    />
                @endif

                {{-- Tombol konfirmasi pengembalian ketika booking sudah dikembalikan --}}
                @if($booking->status === 'menunggu_verifikasi_pengembalian')
                    <x-button 
                        label="Konfirmasi Pengembalian" 
                        icon="o-check-badge" 
                        class="btn-success" 
                        wire:click="confirmReturn"
                        wire:confirm="Konfirmasi pengembalian ini? Status akan diubah menjadi 'selesai' dan motor menjadi 'tersedia'."
                    />
                @endif
            </div>
        </x-slot:actions>
    </x-header>

    <div class="p-4 space-y-6">
        <!-- Grid Layout untuk Responsif -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            
            <!-- Informasi Booking -->
            <x-card title="Informasi Penyewaan" class="h-fit">
                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <x-icon name="o-hashtag" class="w-4 h-4 inline mr-2" />
                            <span class="font-medium">Kode Booking:</span>
                            <div class="mt-1 px-2 py-1 rounded text-mono text-sm">
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
                            <x-icon name="o-user" class="w-4 h-4 mr-2" />
                            Data Penyewa
                        </h4>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-gray-600">Nama:</span>
                                <div class="font-medium">{{ $penyewa->nama }}</div>
                            </div>
                            <div>
                                <span class="text-gray-600">Email:</span>
                                <div class="font-medium">{{ $penyewa->email }}</div>
                            </div>
                            <div>
                                <span class="text-gray-600">No. Telepon:</span>
                                <div class="font-medium">{{ $penyewa->no_telp ?? '-' }}</div>
                            </div>
                            <div>
                                <span class="text-gray-600">Kode User:</span>
                                <div class="font-medium">{{ $penyewa->kode_user }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="border-t pt-4">
                        <h4 class="font-semibold text-sm mb-3 flex items-center">
                            <x-icon name="o-truck" class="w-4 h-4 mr-2" />
                            Data Motor
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
                                <div class="font-medium">{{ $motor->owner->nama ?? '-' }}</div>
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
                                <div class="mt-1 px-2 py-1 rounded text-mono text-sm">
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
                                    <span class="text-gray-600">Jumlah Harus Bayar:</span>
                                    <div class="font-medium text-orange-600">Rp {{ number_format($payment->jumlah_bayar, 0, ',', '.') }}</div>
                                </div>
                                <div>
                                    <span class="text-gray-600">Uang Dibayar:</span>
                                    <div class="font-medium text-blue-600">Rp {{ number_format($payment->uang_bayar, 0, ',', '.') }}</div>
                                </div>
                                <div>
                                    <span class="text-gray-600">Uang Kembalian:</span>
                                    <div class="font-medium text-green-600">Rp {{ number_format($payment->uang_kembalian, 0, ',', '.') }}</div>
                                </div>
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

                        @if($payment->catatan)
                            <div class="border-t pt-4">
                                <h4 class="font-semibold text-sm mb-2 flex items-center">
                                    <x-icon name="o-document-text" class="w-4 h-4 mr-2" />
                                    Catatan
                                </h4>
                                <div class="text-sm p-3 rounded">
                                    {{ $payment->catatan }}
                                </div>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="text-center py-8">
                        <x-icon name="o-exclamation-triangle" class="w-16 h-16 mx-auto text-yellow-500 mb-4" />
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Pembayaran Belum Ada</h3>
                        <p class="text-gray-600">Customer belum melakukan pembayaran untuk booking ini.</p>
                    </div>
                @endif
            </x-card>
        </div>

        <!-- Status Summary -->
        @if($payment)
            {{-- <x-card title="Ringkasan Status" class="bg-gradient-to-r from-blue-50 to-purple-50">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold {{ $booking->status === 'pending' ? 'text-yellow-600' : ($booking->status === 'active' ? 'text-blue-600' : ($booking->status === 'completed' ? 'text-green-600' : 'text-red-600')) }}">
                            {{ ucfirst($booking->status) }}
                        </div>
                        <div class="text-sm text-gray-600">Status Booking</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold {{ $payment->isPaid() ? 'text-green-600' : 'text-red-600' }}">
                            {{ $payment->isPaid() ? 'Lunas' : 'Belum Lunas' }}
                        </div>
                        <div class="text-sm text-gray-600">Status Pembayaran</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold {{ $motor->status === 'tersedia' ? 'text-green-600' : 'text-yellow-600' }}">
                            {{ ucfirst($motor->status) }}
                        </div>
                        <div class="text-sm text-gray-600">Status Motor</div>
                    </div>
                </div>
                
                @if($booking->status === 'pending' && $payment->isPaid())
                    <div class="mt-4 p-3 bg-green-100 border border-green-200 rounded-lg">
                        <div class="flex items-center">
                            <x-icon name="o-check-circle" class="w-5 h-5 text-green-600 mr-2" />
                            <span class="text-green-800 font-medium">Siap untuk diaktifkan! Pembayaran sudah lunas.</span>
                        </div>
                    </div>
                @elseif($booking->status === 'pending' && !$payment->isPaid())
                    <div class="mt-4 p-3 bg-yellow-100 border border-yellow-200 rounded-lg">
                        <div class="flex items-center">
                            <x-icon name="o-clock" class="w-5 h-5 text-yellow-600 mr-2" />
                            <span class="text-yellow-800 font-medium">Menunggu pembayaran dari customer.</span>
                        </div>
                    </div>
                @endif
            </x-card> --}}
        @endif
    </div>
</div>
