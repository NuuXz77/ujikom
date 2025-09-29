<?php

use Livewire\Volt\Component;
use App\Models\User;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

new class extends Component {
    use Toast;

    public bool $resetPasswordModal = false;
    public $userId, $nama, $email, $newPassword;
    public $listeners = ['showResetPasswordModal' => 'openModal'];

    public function openModal($id)
    {
        $user = User::findOrFail($id);
        $this->userId = $user->ID_User;
        $this->nama = $user->nama;
        $this->email = $user->email;
        
        // Generate new random password
        $this->newPassword = $this->generateRandomPassword();
        $this->resetPasswordModal = true;
    }

    public function generateRandomPassword()
    {
        // Generate a random password with 12 characters including uppercase, lowercase, numbers
        return Str::password(12, true, true, true, false);
    }

    public function generateNewPassword()
    {
        $this->newPassword = $this->generateRandomPassword();
    }

    public function resetPassword()
    {
        $user = User::findOrFail($this->userId);
        $user->password = Hash::make($this->newPassword);
        $user->save();

        $this->reset(['userId', 'nama', 'email', 'newPassword']);
        $this->resetPasswordModal = false;
        $this->toast('success', 'Sukses!', 'Password berhasil direset');
        $this->dispatch('refresh');
    }
};
?>

<div>
    <x-mary-modal wire:model="resetPasswordModal" title="Reset Password Pengguna" persistent class="backdrop-blur">
        <div class="mb-4 space-y-4">
            <p>Reset password untuk pengguna berikut:</p>
            
            <div class="bg-base-200 p-3 rounded">
                <p><strong>Nama:</strong> {{ $nama }}</p>
                <p><strong>Email:</strong> {{ $email }}</p>
            </div>

            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <x-icon name="o-exclamation-triangle" class="h-5 w-5 text-yellow-400" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            Password baru akan dihasilkan secara otomatis. Pastikan Anda menyimpan password ini dan memberitahukannya kepada pengguna.
                        </p>
                    </div>
                </div>
            </div>

            <div class="space-y-2">
                <label class="block text-sm font-medium text-gray-700">Password Baru:</label>
                <div class="flex gap-2">
                    <x-input 
                        value="{{ $newPassword }}" 
                        readonly 
                        class="font-mono bg-gray-50"
                    />
                    <x-button 
                        icon="o-arrow-path" 
                        wire:click="generateNewPassword" 
                        class="btn-sm"
                        title="Generate Password Baru"
                    />
                </div>
                <p class="text-xs text-gray-500">Klik tombol refresh untuk menghasilkan password baru</p>
            </div>
        </div>

        <x-slot:actions>
            <x-button label="Batal" @click="$wire.resetPasswordModal = false" />
            <x-button 
                label="Reset Password" 
                wire:click="resetPassword" 
                class="btn-warning" 
                spinner="resetPassword" 
            />
        </x-slot:actions>
    </x-mary-modal>
</div>
