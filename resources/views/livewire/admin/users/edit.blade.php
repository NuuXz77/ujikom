<?php

use Livewire\Volt\Component;
use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Attributes\Validate;

new class extends Component {
    use Toast;

    public $user;

    #[Validate('required|string|max:50')]
    public string $kode_user = '';

    #[Validate('required|string|max:255')]
    public string $nama = '';

    #[Validate('required|email|max:255')]
    public string $email = '';

    #[Validate('nullable|string|max:20')]
    public string $no_telp = '';

    #[Validate('nullable|string|max:100')]
    public string $username = '';

    #[Validate('required|in:admin,pemilik,penyewa')]
    public string $role = '';

    public array $roleOptions = [
        ['id' => 'pemilik', 'name' => 'Pemilik'],
        ['id' => 'penyewa', 'name' => 'Penyewa']
    ];

    public function mount($id)
    {
        $this->user = User::findOrFail($id);
        $this->kode_user = $this->user->kode_user;
        $this->nama = $this->user->nama;
        $this->email = $this->user->email;
        $this->no_telp = $this->user->no_telp ?? '';
        $this->username = $this->user->username ?? '';
        $this->role = $this->user->role;
    }

    public function save()
    {
        $this->validate();

        // Check if email is unique (except for current user)
        $emailExists = User::where('email', $this->email)
            ->where('ID_User', '!=', $this->user->ID_User)
            ->exists();

        if ($emailExists) {
            $this->addError('email', 'Email sudah digunakan oleh pengguna lain.');
            return;
        }

        // Check if kode_user is unique (except for current user)
        $kodeExists = User::where('kode_user', $this->kode_user)
            ->where('ID_User', '!=', $this->user->ID_User)
            ->exists();

        if ($kodeExists) {
            $this->addError('kode_user', 'Kode user sudah digunakan oleh pengguna lain.');
            return;
        }

        // Check if username is unique (except for current user) if provided
        if (!empty($this->username)) {
            $usernameExists = User::where('username', $this->username)
                ->where('ID_User', '!=', $this->user->ID_User)
                ->exists();

            if ($usernameExists) {
                $this->addError('username', 'Username sudah digunakan oleh pengguna lain.');
                return;
            }
        }

        try {
            $this->user->update([
                'kode_user' => $this->kode_user,
                'nama' => $this->nama,
                'email' => $this->email,
                'no_telp' => $this->no_telp ?: null,
                'role' => $this->role
            ]);

            $this->toast('success', 'Sukses!', 'Data pengguna berhasil diperbarui');
            return redirect()->route('users-admin');
        } catch (\Exception $e) {
            $this->toast('error', 'Error!', 'Gagal memperbarui data pengguna: ' . $e->getMessage());
        }
    }
}; ?>

<div>
    <x-header title="Edit Pengguna: {{ $user->nama }}" separator progress-indicator>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Kembali" link="/admin/users" class="btn-ghost" />
        </x-slot:actions>
    </x-header>

    <div class="p-4">
        <form wire:submit.prevent="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Informasi Dasar -->
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold">Informasi Dasar</h2>
                    
                    <x-input 
                        label="Kode User" 
                        wire:model="kode_user" 
                        placeholder="Masukkan kode user"
                        error-field="kode_user"
                        required
                        readonly
                    />
                    
                    <x-input 
                        label="Nama Lengkap" 
                        wire:model="nama" 
                        placeholder="Masukkan nama lengkap"
                        error-field="nama"
                        required
                    />
                    
                    <x-input 
                        label="Email" 
                        type="email" 
                        wire:model="email" 
                        placeholder="Masukkan email"
                        error-field="email"
                        required
                    />
                </div>

                <!-- Informasi Kontak & Role -->
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold">Informasi Kontak & Role</h2>
                    
                    <x-input 
                        label="Nomor Telepon (Opsional)" 
                        wire:model="no_telp" 
                        placeholder="Masukkan nomor telepon"
                        error-field="no_telp"
                    />
                    
                    <x-select 
                        label="Role" 
                        wire:model="role" 
                        :options="$roleOptions" 
                        error-field="role"
                        placeholder="Pilih role pengguna"
                        required
                    />

                    <!-- Informasi readonly -->
                    <x-input label="Tanggal Dibuat" value="{{ $user->created_at->format('d M Y, H:i') }}" readonly />
                    {{-- <x-input label="Terakhir Diupdate" value="{{ $user->updated_at->format('d M Y, H:i') }}" readonly /> --}}
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 flex gap-2">
                <x-button 
                    type="submit" 
                    icon="o-check" 
                    label="Simpan Perubahan" 
                    class="btn-primary"
                    spinner="save"
                />
                <x-button 
                    icon="o-x-mark" 
                    label="Batal" 
                    link="/admin/users" 
                    class="btn-ghost" 
                />
            </div>
        </form>
    </div>
</div>
