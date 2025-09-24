<?php

use Livewire\Volt\Component;
use App\Models\User;
use Mary\Traits\Toast;

new class extends Component {
    use Toast;

    public bool $deleteModal = false;
    public $userId, $nama, $email, $kode_user, $role, $no_telp;
    public $listeners = ['showDeleteModal' => 'openModal'];

    public function openModal($id)
    {
        // Find user by ID
        $user = User::findOrFail($id);
        $this->userId = $user->ID_User;
        $this->nama = $user->nama;
        $this->email = $user->email;
        $this->kode_user = $user->kode_user;
        $this->role = $user->role;
        $this->no_telp = $user->no_telp ?? '-';
        $this->deleteModal = true;
    }

    public function deleteUser()
    {
        User::where('ID_User', $this->userId)->delete();
        $this->reset(['userId', 'nama', 'email', 'kode_user', 'role', 'no_telp']);
        $this->deleteModal = false;
        $this->toast('success', 'Sukses!', 'Data pengguna berhasil dihapus');
        $this->dispatch('refresh');
    }
};
?>

<div>
    <x-mary-modal wire:model="deleteModal" title="Hapus Data Pengguna" persistent class="backdrop-blur">
        <div class="mb-4 space-y-2">
            <p>Yakin ingin menghapus data pengguna berikut?</p>
            <div class="bg-base-200 p-3 rounded">
                <p><strong>Nama:</strong> {{ $nama }}</p>
                <p><strong>Email:</strong> {{ $email }}</p>
                <p><strong>Kode User:</strong> {{ $kode_user }}</p>
                <p><strong>Role:</strong> {{ ucfirst($role) }}</p>
                <p><strong>No. Telp:</strong> {{ $no_telp }}</p>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Batal" @click="$wire.deleteModal = false" />
            <x-button label="Hapus" wire:click="deleteUser" class="btn-error" spinner="deleteUser" />
        </x-slot:actions>
    </x-mary-modal>
</div>
