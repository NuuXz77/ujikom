<?php

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Hash;

new #[Layout('components.layouts.guest')] #[Title('Registration')] class
    //  <-- The same `empty` layout
    extends Component {
    #[Rule('required')]
    public string $nama = '';

    #[Rule('required|email|unique:users')]
    public string $email = '';

    #[Rule('required|confirmed')]
    public string $password = '';

    #[Rule('required')]
    public string $password_confirmation = '';

    #[Rule('required')]
    public string $role = '';
    public $roleOptions = [['id' => 'penyewa', 'name' => 'Penyewa', 'hint' => 'Buat akun untuk penyewa.'], ['id' => 'pemilik', 'name' => 'Pemilik', 'hint' => 'Jadikan uang untuk motormu.']];
    public function mount()
    {
        // It is logged in
        if (auth()->user()) {
            return redirect('/');
        }
    }

    public function register()
    {
        $data = $this->validate();

        // Tentukan awalan kode berdasarkan peran (role)
        $prefix = match ($data['role']) {
            'admin' => 'ADM',
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
        $kode_user = $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        // Buat data user dengan kode_user yang sudah dibuat
        $userData = [
            'kode_user' => $kode_user,
            'nama' => $data['nama'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
        ];

        $user = User::create($userData);

        return redirect('/login');
    }
}; ?>

<div class="md:w-96 mx-auto mt-20">
    <div class="mb-10">
        <x-app-brand />
    </div>

    <x-form wire:submit="register">
        <x-input placeholder="Nama" wire:model="nama" icon="o-user" />
        <x-input placeholder="E-mail" wire:model="email" icon="o-envelope" />
        <x-input placeholder="Password" wire:model="password" type="password" icon="o-key" />
        <x-input placeholder="Confirm Password" wire:model="password_confirmation" type="password" icon="o-key" />
        <x-radio label="Select one option" wire:model="role" :options="$roleOptions" inline />

        <x-slot:actions>
            <x-button label="Sudah memiliki akun?" class="btn-ghost" link="/login" />
            <x-button label="Daftar" type="submit" icon="o-paper-airplane" class="btn-primary" spinner="register" />
        </x-slot:actions>
    </x-form>
</div>