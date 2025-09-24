<?php

use Livewire\Volt\Component;
use Livewire\WithPagination;
use Illuminate\Pagination\LengthAwarePaginator;

new class extends Component {
    use WithPagination;

    public int $perPage = 10;
    public array $sortBy = ['column' => 'nama', 'direction' => 'asc'];
    public array $headers = [
        ['key' => 'no', 'label' => 'No', 'sortable' => false],
        ['key' => 'kode_user', 'label' => 'Kode', 'sortable' => true],
        ['key' => 'nama', 'label' => 'Nama', 'sortable' => true],
        ['key' => 'email', 'label' => 'Email', 'sortable' => true],
        ['key' => 'no_telp', 'label' => 'No Telp', 'sortable' => true],
        // ['key' => 'username', 'label' => 'Username', 'sortable' => true],
        ['key' => 'role', 'label' => 'Role', 'sortable' => true],
        ['key' => 'actions', 'label' => 'Aksi', 'sortable' => false],
    ];

    public string $search = '';
    public string $role = '';// filter per role kecuali admin disembunyikan dari listing
    public array $roleOptions = [];

    public function mount(): void
    {
        $this->roleOptions = \App\Models\User::query()
            ->where('role', '!=', 'admin')
            ->pluck('role')->unique()->sort()->values()->toArray();
    }

    public function getRowsProperty(): LengthAwarePaginator
    {
        return \App\Models\User::query()
            ->where('role', '!=', 'admin')
            ->when($this->search, function($q){
                $q->where(function($w){
                    $w->where('nama','like',"%{$this->search}%")
                      ->orWhere('email','like',"%{$this->search}%")
                      ->orWhere('username','like',"%{$this->search}%")
                      ->orWhere('kode_user','like',"%{$this->search}%");
                });
            })
            ->when($this->role, fn($q) => $q->where('role', $this->role))
            ->orderBy($this->sortBy['column'], $this->sortBy['direction'])
            ->paginate($this->perPage);
    }

    public function setRole(string $role): void { $this->role = $role; $this->resetPage(); }
}; ?>

<div>
    <x-header title="Daftar Pengguna" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input icon="o-magnifying-glass" placeholder="Cari nama / email / kode..." wire:model.live.debounce.500ms="search" />
        </x-slot:middle>
        <x-slot:actions>
            <x-dropdown label="Role">
                <x-menu-item icon="o-x-circle" title="Semua" wire:click="setRole('')" />
                <x-menu-separator />
                @foreach($roleOptions as $r)
                    <x-menu-item title="{{ ucfirst($r) }}" wire:click="setRole('{{ $r }}')" />
                @endforeach
            </x-dropdown>
            <x-button icon="o-plus" class="btn-primary" link="/admin/users/create" />
        </x-slot:actions>
    </x-header>

    <x-table :headers="$headers" :rows="$this->rows" :sort-by="$sortBy" :per-page="$perPage" :per-page-values="[5,10,20,50]" with-pagination>
        @scope('cell_no', $user, $loop)
            {{ ($this->rows->currentPage() - 1) * $this->rows->perPage() + $loop->iteration }}
        @endscope
        @scope('cell_kode_user', $user)
            {{ $user->kode_user ?? '-' }}
        @endscope
        @scope('cell_nama', $user)
            {{ $user->nama }}
        @endscope
        @scope('cell_email', $user)
            {{ $user->email }}
        @endscope
        @scope('cell_no_telp', $user)
            {{ $user->no_telp ?? '-' }}
        @endscope
        {{-- @scope('cell_username', $user)
            {{ $user->username }}
        @endscope --}}
        @scope('cell_role', $user)
            <x-badge value="{{ ucfirst($user->role) }}" class="badge badge-primary badge-soft" />
        @endscope
        @scope('cell_actions', $user)
            <x-dropdown>
                <x-slot:trigger>
                    <x-button icon="m-ellipsis-vertical" class="btn-circle" />
                </x-slot:trigger>
                <x-menu-item title="Detail" icon="o-eye" link="/admin/users/detail/{{ $user->ID_User }}" />
                <x-menu-item title="Edit" icon="o-pencil" link="/admin/users/{{ $user->ID_User }}/edit" />
                <x-menu-item title="Hapus" icon="o-trash" wire:click="$dispatch('showDeleteModal', { id: {{ $user->ID_User }} })" class="text-red-500" />
            </x-dropdown>
        @endscope
        <x-slot:empty>
            <x-icon name="o-users" label="Data pengguna tidak ditemukan." />
        </x-slot:empty>
    </x-table>
</div>
