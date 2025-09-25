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
                <x-button label="Booking" class="btn-primary btn-soft" icon="o-clipboard-document-list" link="/bookings" />
                <x-dropdown>
                    <x-slot:trigger>
                        <x-button icon="o-user-circle" label="{{ $user->nama ?? ($user->full_name ?? 'User') }}"
                            class="btn btn-ghost" />
                    </x-slot:trigger>
                    <x-menu-item title="Profile" icon="o-user" link="/profile" />
                    <x-menu-item title="Transaksi" icon="o-credit-card" link="/history" />
                    <x-menu-separator />
                    <livewire:auth.logout>
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

                        <x-list-item :item="$user" value="nama" sub-value="email" no-separator no-hover
                            class="-mx-2 !-my-2 rounded">
                            <x-slot:avatar>
                                <x-icon name="o-user-circle" class="w-8 h-8" />
                            </x-slot:avatar>
                        </x-list-item>

                        <x-menu-separator />
                    @endif

                    @if (auth()->user() && auth()->user()->role === 'admin')
                        <x-menu-item title="Dashboard" icon="o-squares-2x2" link="/admin/dashboard" />
                        <x-menu-item title="Data Motor" icon="o-truck" link="/admin/motors" />
                        <x-menu-item title="Data Sewa" icon="o-clipboard-document-list" link="/admin/bookings" />
                        {{-- <x-menu-item title="Pengembalian" icon="o-arrow-uturn-left" link="/admin/returns" /> --}}
                        <x-menu-item title="Data User" icon="o-users" link="/admin/users" />
                        <x-menu-item title="Bagi Hasil" icon="o-chart-pie" link="/admin/revenue" />
                        <x-menu-item title="Transaksi" icon="o-credit-card" link="/admin/history" />
                    @endif

                    @if (auth()->user() && auth()->user()->role === 'pemilik')
                        <x-menu-item title="Dashboard" icon="o-squares-2x2" link="/owner/dashboard" />
                        <x-menu-item title="Motor" icon="o-truck" link="/owner/motors" />
                        <x-menu-item title="Laporan" icon="o-document-chart-bar" link="/owner/revenue" />
                    @endif

                    <x-menu-sub title="Settings" icon="o-cog-6-tooth">
                        <livewire:auth.logout />
                    </x-menu-sub>
                </x-menu>
            </x-slot:sidebar>
        @endif

        {{-- CONTENT --}}
        <x-slot:content>
            {{ $slot }}
        </x-slot:content>
    </x-main>

    {{--  TOAST area --}}
    <x-toast />
</body>

</html>