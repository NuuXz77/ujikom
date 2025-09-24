<?php

use Livewire\Volt\Component;
use App\Models\User;

new class extends Component {
    public $user;

    public function mount($id)
    {
        $this->user = User::findOrFail($id);
    }
}; ?>

<div>
    <x-header title="Detail Pengguna: {{ $user->nama }}" separator progress-indicator>
        <x-slot:actions>
            <div class="flex items-center gap-2">
                <x-button icon="o-arrow-left" label="Kembali" link="/admin/users" class="btn-ghost" />
                <x-badge value="{{ ucfirst($user->role) }}" class="badge badge-primary badge-soft" />
            </div>
        </x-slot:actions>
    </x-header>

    <div class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Informasi Dasar -->
            <div class="space-y-4">
                <h2 class="text-lg font-semibold">Informasi Dasar</h2>
                
                <x-input label="Kode User" value="{{ $user->kode_user }}" readonly />
                <x-input label="Nama Lengkap" value="{{ $user->nama }}" readonly />
                <x-input label="Email" value="{{ $user->email }}" readonly />
                <x-input label="Username" value="{{ $user->username ?? '-' }}" readonly />
            </div>

            <!-- Informasi Kontak & Role -->
            <div class="space-y-4">
                <h2 class="text-lg font-semibold">Informasi Kontak & Role</h2>
                
                <x-input label="Nomor Telepon" value="{{ $user->no_telp ?? '-' }}" readonly />
                <x-input label="Role" value="{{ ucfirst($user->role) }}" readonly />
                <x-input label="Tanggal Dibuat" value="{{ $user->created_at->format('d M Y, H:i') }}" readonly />
                <x-input label="Terakhir Diupdate" value="{{ $user->updated_at->format('d M Y, H:i') }}" readonly />
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 flex gap-2">
            <x-button icon="o-pencil" label="Edit" link="/admin/users/{{ $user->ID_User }}/edit" class="btn-primary" />
            <x-button icon="o-key" label="Reset Password" wire:click="$dispatch('showResetPasswordModal', { id: {{ $user->ID_User }} })" class="btn-warning" />
            <x-button icon="o-trash" label="Hapus" wire:click="$dispatch('showDeleteModal', { id: {{ $user->ID_User }} })" class="btn-error" />
        </div>
    </div>

    <!-- Include modal components -->
    <livewire:admin.users.delete />
    <livewire:admin.users.reset-password />
</div>
