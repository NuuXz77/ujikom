<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Title;
use Livewire\Volt\Component;

new #[Layout('components.layouts.guest')] #[Title('Login')] class
    //  <-- Here is the `empty` layout
    extends Component {
    #[Rule('required|email')]
    public string $email = '';

    #[Rule('required')]
    public string $password = '';

    public function mount()
    {
        // It is logged in
        if (auth()->user()) {
            return redirect('/');
        }
    }

    public function login()
    {
        $credentials = $this->validate();

        if (auth()->attempt($credentials)) {
            request()->session()->regenerate();

            $user = auth()->user();
            if ($user->role === 'admin') {
                return redirect()->route('dashboard-admin');
            } elseif ($user->role === 'penyewa') {
                return redirect()->route('dashboard-penyewa');
            } elseif ($user->role === 'pemilik') {
                return redirect()->route('dashboard-pemilik');
            } else {
                return redirect('/');
            }
        }

        $this->addError('email', 'The provided credentials do not match our records.');
    }
}; ?>

<div class="md:w-96 mx-auto mt-20">
    <div class="mb-10">
        <x-app-brand />
    </div>

    <x-form wire:submit="login">
        <x-input placeholder="E-mail" wire:model="email" icon="o-envelope" />
        <x-input placeholder="Password" wire:model="password" type="password" icon="o-key" />

        <x-slot:actions>
            <x-button label="Buat akun" class="btn-ghost" link="/register" />
            <x-button label="Masuk" type="submit" icon="o-paper-airplane" class="btn-primary" spinner="login" />
        </x-slot:actions>
    </x-form>
    
    <!-- Catatan User Seed -->
    <div class="mt-6 p-4 bg-yellow-50 border border-yellow-300 rounded text-sm text-yellow-800">
        <strong>Catatan Login User Seed:</strong>
        <ul class="mt-2 list-disc pl-5">
            <li><b>Admin:</b> admin@a.com | password: password123</li>
            <li><b>Pemilik:</b> pemilik@a.com | password: password123</li>
            <li><b>Penyewa:</b> penyewa@a.com | password: password123</li>
        </ul>
        <span class="block mt-2">Gunakan data di atas untuk login.</span>
        <span class="block mt-2">Jika ingin menggunakan akun lain klik <b>daftar.</b></span>
    </div>
</div>