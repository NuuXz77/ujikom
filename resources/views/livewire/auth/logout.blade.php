<?php

use Livewire\Volt\Component;

new class extends Component {
    public function logout()
    {
        // $user = Auth::user();
        // $user->status = 'Tidak Aktif';
        // $user->save();

        Auth::logout();

        session()->invalidate();
        session()->regenerateToken();

        return redirect('/login');
    }
}; ?>

<div>
    <x-menu-item title="Logout" wire:click.stop="logout" spinner="logout" icon="o-arrow-left-on-rectangle" />
</div>