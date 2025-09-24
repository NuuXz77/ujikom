<?php

use Livewire\Volt\Component;
use App\Models\Penyewaan;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use Toast;

    public bool $cancelModal = false;
    public $bookingId, $kode, $motor, $plat, $mulai, $selesai, $durasi, $harga;
    public $listeners = ['showCancelModal' => 'openModal'];

    public function openModal($id)
    {
        $id = is_array($id) && isset($id) ? $id : $id;
        $booking = Penyewaan::with('motor')
            ->where('ID_Penyewaan', $id)
            ->where('penyewa_id', Auth::id())
            ->whereIn('status', ['pending','disewa'])
            ->firstOrFail();

        $this->bookingId = $booking->ID_Penyewaan;
        $this->kode = '#'.$booking->ID_Penyewaan;
        $this->motor = $booking->motor?->merk;
        $this->plat = $booking->motor?->no_plat;
        $this->mulai = $booking->tanggal_mulai;
        $this->selesai = $booking->tanggal_selesai;
        $this->durasi = ucfirst($booking->tipe_durasi);
        $this->harga = $booking->harga;
        $this->cancelModal = true;
    }

    public function cancelBooking()
    {
        $booking = Penyewaan::where('ID_Penyewaan', $this->bookingId)
            ->where('penyewa_id', Auth::id())
            ->where('status', 'pending')
            ->first();
        if($booking){
            $booking->update(['status' => 'dibatalkan']);
            $this->toast('success', 'Dibatalkan', 'Booking berhasil dibatalkan.');
            $this->dispatch('refresh');
        } else {
            $this->toast('warning', 'Gagal', 'Booking tidak bisa dibatalkan.');
        }
        $this->reset(['bookingId','kode','motor','plat','mulai','selesai','durasi','harga']);
        $this->cancelModal = false;
    }
}; ?>

<div>
    <x-mary-modal wire:model="cancelModal" title="Batalkan Booking" persistent class="backdrop-blur">
        <div class="mb-4 space-y-2 text-sm">
            <p>Anda yakin ingin membatalkan booking berikut?</p>
            <div class="bg-base-200 p-3 rounded space-y-1">
                <p><strong>Kode:</strong> {{ $kode }}</p>
                <p><strong>Motor:</strong> {{ $motor }} ({{ $plat }})</p>
                <p><strong>Periode:</strong> {{ $mulai }} s/d {{ $selesai }} ({{ $durasi }})</p>
                <p><strong>Total:</strong> Rp {{ number_format($harga ?? 0,0,',','.') }}</p>
            </div>
            <p class="text-xs text-amber-600">Aksi ini tidak dapat dikembalikan. Status akan menjadi <strong>DIBATALKAN</strong>.</p>
        </div>
        <x-slot:actions>
            <x-button label="Tutup" @click="$wire.cancelModal = false" />
            <x-button label="Batalkan" wire:click="cancelBooking" class="btn-error" spinner="cancelBooking" />
        </x-slot:actions>
    </x-mary-modal>
</div>
