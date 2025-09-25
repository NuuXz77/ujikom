<?php

use Livewire\Volt\Component;
use App\Models\Penyewaan;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public bool $deleteModal = false;
    public $bookingId, $kode, $penyewa, $motor, $tanggal_mulai, $tanggal_selesai, $harga;
    public $listeners = ['showDeleteModal' => 'openModal'];

    public function openModal($id)
    {
        $booking = Penyewaan::with(['user', 'motor'])->findOrFail($id);
        $this->bookingId = $booking->ID_Penyewaan;
        $this->kode = '#' . $booking->ID_Penyewaan;
        $this->penyewa = $booking->user->nama ?? '-';
        $this->motor = $booking->motor->merk . ' (' . $booking->motor->no_plat . ')';
        $this->tanggal_mulai = \Carbon\Carbon::parse($booking->tanggal_mulai)->format('d/m/Y');
        $this->tanggal_selesai = \Carbon\Carbon::parse($booking->tanggal_selesai)->format('d/m/Y');
        $this->harga = 'Rp ' . number_format($booking->harga, 0, ',', '.');
        $this->deleteModal = true;
    }

    public function deleteBooking()
    {
        try {
            $booking = Penyewaan::findOrFail($this->bookingId);
            
            // Pastikan hanya booking dengan status 'selesai' yang bisa dihapus
            if ($booking->status !== 'selesai') {
                $this->toast('error', 'Error!', 'Hanya booking dengan status selesai yang dapat dihapus.');
                return;
            }

            $booking->delete();
            
            $this->reset(['bookingId', 'kode', 'penyewa', 'motor', 'tanggal_mulai', 'tanggal_selesai', 'harga']);
            $this->deleteModal = false;
            $this->toast('success', 'Sukses!', 'Data booking berhasil dihapus');
            $this->dispatch('refresh');
            
        } catch (\Exception $e) {
            $this->toast('error', 'Error!', 'Gagal menghapus booking: ' . $e->getMessage());
        }
    }
};
?>

<div>
    <x-modal wire:model="deleteModal" title="Hapus Data Booking" persistent class="backdrop-blur">
        <div class="mb-4 space-y-2">
            <p class="text-gray-700">Yakin ingin menghapus data booking berikut?</p>
            <div class="bg-base-200 p-4 rounded-lg space-y-2">
                <p><strong>Kode:</strong> {{ $kode }}</p>
                <p><strong>Penyewa:</strong> {{ $penyewa }}</p>
                <p><strong>Motor:</strong> {{ $motor }}</p>
                <p><strong>Periode:</strong> {{ $tanggal_mulai }} - {{ $tanggal_selesai }}</p>
                <p><strong>Harga:</strong> {{ $harga }}</p>
            </div>
            <p class="text-red-600 text-sm">
                <strong>Peringatan:</strong> Data yang sudah dihapus tidak dapat dikembalikan!
            </p>
        </div>

        <x-slot:actions>
            <x-button label="Batal" @click="$wire.deleteModal = false" class="btn-ghost" />
            <x-button label="Hapus" wire:click="deleteBooking" class="btn-error" spinner="deleteBooking" />
        </x-slot:actions>
    </x-modal>
</div>
