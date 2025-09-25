<?php

use Livewire\Volt\Component;
use App\Models\Penyewaan;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use Toast;

    public bool $deleteModal = false;
    public $bookingId, $kode, $motor, $plat, $mulai, $selesai, $harga, $status;
    public $listeners = ['showDeleteModal' => 'openModal'];

    public function openModal($id)
    {
        // Pastikan booking milik user dan masih bisa dihapus (pending)
        $booking = Penyewaan::with('motor')
            ->where('ID_Penyewaan', $id)
            ->where('penyewa_id', Auth::id())
            ->where('status', 'pending')
            ->firstOrFail();
            
        $this->bookingId = $booking->ID_Penyewaan;
        $this->kode = '#' . $booking->ID_Penyewaan;
        $this->motor = $booking->motor?->merk;
        $this->plat = $booking->motor?->no_plat;
        $this->mulai = $booking->tanggal_mulai;
        $this->selesai = $booking->tanggal_selesai;
        $this->harga = $booking->harga;
        $this->status = $booking->status;
        $this->deleteModal = true;
    }

    public function deleteBooking()
    {
        try {
            $booking = Penyewaan::where('ID_Penyewaan', $this->bookingId)
                ->where('penyewa_id', Auth::id())
                ->where('status', 'pending')
                ->first();

            if (!$booking) {
                $this->error('Booking tidak ditemukan atau tidak bisa dihapus.');
                return;
            }

            // Hapus booking
            $booking->delete();
            
            $this->reset(['bookingId', 'kode', 'motor', 'plat', 'mulai', 'selesai', 'harga', 'status']);
            $this->deleteModal = false;
            $this->success('Booking berhasil dihapus.');
            $this->dispatch('refresh');

        } catch (\Exception $e) {
            $this->error('Gagal menghapus booking: ' . $e->getMessage());
        }
    }

    public function closeModal()
    {
        $this->reset(['bookingId', 'kode', 'motor', 'plat', 'mulai', 'selesai', 'harga', 'status']);
        $this->deleteModal = false;
    }
}; ?>

<div>
    <x-modal wire:model="deleteModal" title="Hapus Booking" persistent class="backdrop-blur">
        <div class="mb-4 space-y-4">
            <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                <div class="flex items-start">
                    <x-icon name="o-exclamation-triangle" class="w-6 h-6 text-red-500 mr-3 mt-0.5" />
                    <div>
                        <h4 class="font-medium text-red-800 mb-2">Peringatan!</h4>
                        <p class="text-sm text-red-700 mb-3">Anda akan menghapus booking berikut secara permanen:</p>
                        
                        <div class="bg-white p-3 rounded border">
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Kode:</span>
                                    <span class="font-medium">{{ $kode }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Motor:</span>
                                    <span class="font-medium">{{ $motor }} ({{ $plat }})</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Periode:</span>
                                    <span class="font-medium">{{ $mulai }} s/d {{ $selesai }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Total:</span>
                                    <span class="font-medium text-green-600">Rp {{ number_format($harga ?? 0, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3 text-xs text-red-600">
                            <p>• Booking akan dihapus secara permanen</p>
                            <p>• Motor akan tersedia kembali untuk pengguna lain</p>
                            <p>• Aksi ini tidak dapat dibatalkan</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-yellow-50 p-3 rounded-lg border border-yellow-200">
                <div class="flex items-start">
                    <x-icon name="o-information-circle" class="w-5 h-5 text-yellow-500 mr-2 mt-0.5" />
                    <div class="text-sm text-yellow-700">
                        <p class="font-medium">Catatan:</p>
                        <p>Booking hanya bisa dihapus jika masih berstatus "Pending". Jika sudah dibayar, gunakan fitur "Batalkan" sebagai gantinya.</p>
                    </div>
                </div>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Batal" wire:click="closeModal" class="btn-outline" />
            <x-button 
                label="Ya, Hapus Booking" 
                wire:click="deleteBooking" 
                class="btn-error" 
                spinner="deleteBooking"
                wire:confirm="Apakah Anda benar-benar yakin ingin menghapus booking ini?"
            />
        </x-slot:actions>
    </x-modal>
</div>
