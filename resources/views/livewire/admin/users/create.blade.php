<?php

use Livewire\Volt\Component;
use App\Models\User;
use Mary\Traits\Toast;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

new class extends Component {
    use Toast;

    #[Validate('required|string|max:255')]
    public string $nama = '';

    #[Validate('required|email|max:255|unique:users,email')]
    public string $email = '';

    #[Validate('nullable|string|max:20')]
    public string $no_telp = '';


    #[Validate('required|in:pemilik,penyewa')]
    public string $role = 'penyewa';

    public string $kode_user = ''; // Will be auto-generated

    #[Validate('required|string|min:8')]
    public string $password = '';

    public array $roleOptions = [['id' => 'pemilik', 'name' => 'Pemilik'], ['id' => 'penyewa', 'name' => 'Penyewa']];

    public function mount()
    {
        // Generate random password by default
        $this->password = Str::password(12, true, true, true, false);
        // Generate kode_user based on default role
        $this->generateKodeUser();
    }

    public function generatePassword()
    {
        $this->password = Str::password(12, true, true, true, false);
    }

    public function updatedRole()
    {
        // Generate new kode_user when role changes
        $this->generateKodeUser();
    }

    private function generateKodeUser()
    {
        // Tentukan awalan kode berdasarkan peran (role)
        $prefix = match ($this->role) {
            'pemilik' => 'OWN',
            'penyewa' => 'RND',
            default => 'USR', // Fallback jika peran tidak sesuai
        };

        // Ambil kode_user terakhir dari database untuk peran yang sama
        $lastUser = User::where('kode_user', 'like', $prefix . '%')
            ->orderBy('kode_user', 'desc')
            ->first();

        // Tentukan nomor urut berikutnya
        if ($lastUser) {
            $lastNumber = (int) substr($lastUser->kode_user, 3);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // Format kode_user dengan padding nol di depan
        $this->kode_user = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }

    public function save()
    {
        $this->validate();

        try {
            User::create([
                'kode_user' => $this->kode_user,
                'nama' => $this->nama,
                'email' => $this->email,
                'no_telp' => $this->no_telp ?: null,
                'role' => $this->role,
                'password' => Hash::make($this->password),
            ]);

            $this->toast('success', 'Sukses!', 'Pengguna baru berhasil dibuat');
            return redirect()->route('users-admin');
        } catch (\Exception $e) {
            $this->toast('error', 'Error!', 'Gagal membuat pengguna baru: ' . $e->getMessage());
        }
    }
}; ?>

<div>
    <x-header title="Tambah Pengguna Baru" separator progress-indicator>
        <x-slot:actions>
            <x-button icon="o-arrow-left" label="Kembali" link="/admin/users" class="btn-ghost" />
        </x-slot:actions>
    </x-header>

    <div class="p-4 max-w-6xl mx-auto">
        <x-form wire:submit.prevent="save">
            <div class="rounded-lg bg-base-100 p-6 mb-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Kolom Kiri - Informasi Dasar -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 mb-4">
                            <x-icon name="o-user" class="w-5 h-5 text-primary" />
                            <h2 class="text-lg font-semibold">Informasi Dasar</h2>
                        </div>

                        <!-- Auto Generated Kode User -->
                        <div class="p-3 rounded-lg">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kode User (Auto
                                Generate)</label>
                            <div class="flex items-center gap-2">
                                <code class="text-lg font-mono px-3 py-2 rounded">{{ $kode_user }}</code>
                                <x-badge value="{{ ucfirst($role) }}" class="badge badge-primary badge-soft" />
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Kode akan berubah otomatis saat role diganti</p>
                        </div>

                        <x-input label="Nama Lengkap" wire:model="nama" placeholder="Masukkan nama lengkap"
                            error-field="nama" icon="o-user" required />

                        <x-input label="Email" type="email" wire:model="email" placeholder="Masukkan email"
                            error-field="email" icon="o-envelope" required hint="Email harus unik dan valid" />

                        {{-- <x-input 
                            label="Username (Opsional)" 
                            wire:model="username" 
                            placeholder="Masukkan username"
                            error-field="username"
                            icon="o-at-symbol"
                            hint="Username bersifat opsional dan harus unik jika diisi"
                        /> --}}
                        <x-button type="submit" icon="o-plus" label="Buat Pengguna" class="btn-primary btn-wide"
                            spinner="save" />
                    </div>

                    <!-- Kolom Kanan - Informasi Kontak & Role -->
                    <div class="space-y-4">
                        <div class="flex items-center gap-2 mb-4">
                            <x-icon name="o-cog-6-tooth" class="w-5 h-5 text-primary" />
                            <h2 class="text-lg font-semibold">Kontak & Role</h2>
                        </div>

                        <x-input label="Nomor Telepon (Opsional)" wire:model="no_telp"
                            placeholder="Masukkan nomor telepon" error-field="no_telp" icon="o-phone" />

                        <x-select label="Role Pengguna" wire:model.live="role" :options="$roleOptions" error-field="role"
                            icon="o-shield-check" placeholder="Pilih role pengguna" required
                            hint="Kode user akan berubah otomatis sesuai role" />

                        <!-- Password Section -->
                        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 rounded-r-lg">
                            <div class="flex items-start">
                                <x-icon name="o-key" class="w-5 h-5 text-yellow-600 mt-0.5 mr-2" />
                                <div class="flex-1">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                                    <div class="flex gap-2">
                                        <x-input wire:model="password" type="text"
                                            placeholder="Password auto-generate" error-field="password"
                                            class="font-mono text-sm" readonly />
                                        <x-button icon="o-arrow-path" wire:click="generatePassword"
                                            class="btn-sm btn-outline btn-warning" title="Generate Password Baru"
                                            type="button" />
                                    </div>
                                    <p class="text-xs text-yellow-700 mt-1">
                                        <x-icon name="o-exclamation-triangle" class="w-4 h-4 inline mr-1" />
                                        Password akan di-generate otomatis. Pastikan menyimpan password ini!
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Action Buttons -->
            {{-- <div class="rounded-lg shadow-sm p-6">
                <div class="flex flex-col sm:flex-row gap-3 justify-end">
                    <x-button 
                        icon="o-x-mark" 
                        label="Batal" 
                        link="/admin/users" 
                        class="btn-ghost" 
                    />
                </div>
            </div> --}}

        </x-form>
    </div>
</div>
