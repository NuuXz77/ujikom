<?php

use Livewire\Volt\Component;
use App\Models\Motors;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public bool $deleteModal = false;
    public $motorId, $merk, $tipe_cc, $no_plat, $owner_nama;
    public $listeners = ['showDeleteModal' => 'openModal'];

    public function openModal($id)
    {
        // Payload bisa berupa id langsung atau array { id: <ID> }
        $motor = Motors::with('owner')->findOrFail($id);
        $this->motorId = $motor->ID_Motor;
        $this->merk = $motor->merk;
        $this->tipe_cc = $motor->tipe_cc;
        $this->no_plat = $motor->no_plat;
        $this->owner_nama = $motor->owner->nama ?? '-';
        $this->deleteModal = true;
    }

    public function deleteMotor()
    {
        Motors::where('ID_Motor', $this->motorId)->delete();
        $this->reset(['motorId', 'merk', 'tipe_cc', 'no_plat', 'owner_nama']);
        $this->deleteModal = false;
        $this->toast('success', 'Sukses!', 'Data motor berhasil dihapus');
        $this->dispatch('refresh');
    }
};
?>

<div>
    <x-mary-modal wire:model="deleteModal" title="Hapus Data Motor" persistent class="backdrop-blur">
        <div class="mb-4 space-y-2">
            <p>Yakin ingin menghapus data motor berikut?</p>
            <div class="bg-base-200 p-3 rounded">
                <p><strong>Merk:</strong> {{ $merk }}</p>
                <p><strong>CC:</strong> {{ $tipe_cc }}</p>
                <p><strong>Plat:</strong> {{ $no_plat }}</p>
                <p><strong>Pemilik:</strong> {{ $owner_nama }}</p>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Batal" @click="$wire.deleteModal = false" />
            <x-button label="Hapus" wire:click="deleteMotor" class="btn-error" spinner="deleteMotor" />
        </x-slot:actions>
    </x-mary-modal>
</div>