<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($title) ? $title . ' - ' . config('app.name') : config('app.name') }}</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Cropper.js --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.1/cropper.min.css" />

    {{-- Sortable.js --}}
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.1/Sortable.min.js"></script>

    <script type="text/javascript" src="https://cdn.jsdelivr.net/gh/robsontenorio/mary@0.44.2/libs/currency/currency.js">
    </script>
</head>

<body class="min-h-screen font-sans antialiased bg-base-200">

    {{-- NAVBAR mobile only --}}
    @if ($user = auth()->user()->role === 'admin' || ($user = auth()->user()->role === 'pemilik'))
        <x-nav sticky class="lg:hidden">
            <x-slot:brand>
                <x-app-brand />
            </x-slot:brand>
            <x-slot:actions>
                <label for="main-drawer" class="lg:hidden me-3">
                    <x-icon name="o-bars-3" class="cursor-pointer" />
                </label>
            </x-slot:actions>
        </x-nav>
    @endif

    @if (auth()->user() && auth()->user()->role === 'penyewa')
        {{-- NAVBAR for penyewa --}}
        <x-nav sticky>
            <x-slot:brand>
                <x-app-brand />
            </x-slot:brand>
            <x-slot:actions>
                @php
                    $user = auth()->user();
                @endphp
                <x-button label="Booking" class="btn-primary btn-soft" icon="o-shopping-cart" link="/bookings" />
                <x-dropdown>
                    <x-slot:trigger>
                        {{-- <div class="flex items-center gap-2 cursor-pointer">
                            <x-icon name="o-user" class="w-6 h-6" />
                            <span class="font-semibold">{{ $user->name ?? $user->full_name ?? 'User' }}</span>
                        </div> --}}
                        <x-button icon="o-user-circle" label="{{ $user->nama ?? ($user->full_name ?? 'User') }}"
                            class="btn btn-ghost" />
                        </x-slot:trigger>
                        <x-menu-item title="Profile" icon="o-user" link="/profile" />
                        <x-menu-item title="Transaksi" icon="o-shopping-cart" link="/rented-history" />
                    <x-menu-separator />
                    <livewire:auth.logout>
                        {{-- <x-menu-item title="Logout" icon="o-arrow-right-on-rectangle" link="/logout" /> --}}
                </x-dropdown>
            </x-slot:actions>
        </x-nav>
    @endif
    {{-- MAIN --}}
    <x-main>
        @if ($user = auth()->user()->role === 'admin' || ($user = auth()->user()->role === 'pemilik'))
            {{-- SIDEBAR --}}
            <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-100 lg:bg-inherit">

                {{-- BRAND --}}
                <x-app-brand class="px-5 pt-4" />

                {{-- MENU --}}
                <x-menu activate-by-route>

                    {{-- User --}}
                    @if ($user = auth()->user())
                        <x-menu-separator />

                        <x-list-item :item="$user" value="name" sub-value="email" no-separator no-hover
                            class="-mx-2 !-my-2 rounded">
                            <x-slot:actions>
                                {{-- <livewire:auth.logout /> --}}
                            </x-slot:actions>
                        </x-list-item>

                        <x-menu-separator />
                    @endif

                    @if (auth()->user() && auth()->user()->role === 'admin')
                        <x-menu-item title="Dashboard" icon="o-home" link="/admin/dashboard" />
                        <x-menu-item title="Data Motor" icon="o-truck" link="/admin/motors" />

                        {{-- <x-menu-sub title="Manajemen Motors" icon="o-truck">
                            <x-menu-item title="Aktif" icon="o-check-circle" link="/manajemen-motors/active" />
                            <x-menu-item title="Disewa" icon="o-clock" link="/manajemen-motors/rented" />
                            <x-menu-item title="Verifikasi" icon="o-shield-check" link="/manajemen-motors/verification" />
                        </x-menu-sub> --}}

                        <x-menu-item title="Data Sewa" icon="o-truck" link="/admin/bookings" />

                        {{-- <x-menu-sub title="Manajemen Penyewaan" icon="o-truck">
                            <x-menu-item title="Perlu Konfirmasi" icon="o-check-circle" link="/manajemen-motors/active" />
                            <x-menu-item title="Disewa" icon="o-clock" link="/manajemen-motors/rented" />
                            <x-menu-item title="Kadaluarsa" icon="o-shield-check" link="/manajemen-motors/verification" />
                        </x-menu-sub> --}}
                        
                        <x-menu-item title="Data User" icon="o-user-group" link="/admin/users" />
                        <x-menu-item title="Bagi Hasil" icon="o-currency-dollar" link="/admin/revenue" />
                        <x-menu-item title="Transaksi" icon="o-currency-dollar" link="/admin/history" />

                        {{-- <x-menu-sub title="Data Users" icon="o-user-group">
                            <x-menu-item title="Pemilik" icon="o-user-group" link="/manajemen-users?role=pemilik" />
                            <x-menu-item title="Penyewa" icon="o-user" link="/manajemen-users?role=penyewa" />
                        </x-menu-sub> --}}
                    @endif

                    @if (auth()->user() && auth()->user()->role === 'pemilik')
                        <x-menu-item title="Dashboard" icon="o-home" link="/owner/dashboard" />
                        <x-menu-item title="Motor" icon="o-truck" link="/owner/motors" />
                        <x-menu-item title="Laporan" icon="o-document-text" link="/owner/revenue" />
                    @endif

                    @if (auth()->user() && auth()->user()->role === 'penyewa')
                    @endif
                    {{-- <x-menu-item title="Hello" icon="o-sparkles" link="/hallo" /> --}}

                    <x-menu-sub title="Settings" icon="o-cog-6-tooth">
                        {{-- <x-menu-item title="Wifi" icon="o-wifi" link="####" />
                        <x-menu-item title="Archives" icon="o-archive-box" link="####" /> --}}
                        <livewire:auth.logout />
                    </x-menu-sub>
                </x-menu>
            </x-slot:sidebar>
        @endif

        {{-- CONTENT --}}
        {{-- The `$slot` goes here --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    {{--  TOAST area --}}
    <x-toast />
</body>

</html>