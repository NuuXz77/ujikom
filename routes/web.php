<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::middleware(['guest'])->group(function () {
    Volt::route('/login', 'auth.login')->name('login');
    Volt::route('/register', 'auth.register')->name('register');
});

Route::middleware(['auth'])->group(function () {
    Route::middleware('role:admin')->group(function () {
        Volt::route('/admin/dashboard', 'admin.dashboard.index')->name('dashboard-admin');

        Volt::route('/admin/motors', 'admin.motors.index')->name('motors-admin');
        Volt::route('/admin/motors/create', 'admin.motors.create')->name('create-motors-admin');
        Volt::route('/admin/motors/detail/{id}', 'admin.motors.detail')->name('detail-motors-admin');
        Volt::route('/admin/motors/edit/{id}', 'admin.motors.edit')->name('edit-motors-admin');

        Volt::route('/admin/bookings', 'admin.bookings.index')->name('bookings-admin');
        Volt::route('/admin/bookings/detail/{id}', 'admin.bookings.detail')->name('detail-bookings-admin');
        Volt::route('/admin/bookings/edit/{id}', 'admin.bookings.edit')->name('edit-bookings-admin');

        Volt::route('/admin/returns', 'admin.returns.index')->name('returns-admin');

        Volt::route('/admin/users', 'admin.users.index')->name('users-admin');
        Volt::route('/admin/users/create', 'admin.users.create')->name('create-users-admin');
        Volt::route('/admin/users/detail/{id}', 'admin.users.detail')->name('detail-users-admin');
        Volt::route('/admin/users/{id}/edit', 'admin.users.edit')->name('edit-users-admin');

        Volt::route('/admin/revenue', 'admin.revenue.index')->name('revenue-admin');

        Volt::route('/admin/history', 'admin.history.index')->name('history-admin');
        Volt::route('/admin/history/detail/{id}', 'admin.history.detail')->name('detail-history-admin');
    });
    Route::middleware('role:pemilik')->group(function () {
        Volt::route('/owner/dashboard', 'pemilik.dashboard.index')->name('dashboard-pemilik');

        Volt::route('/owner/motors', 'pemilik.motors.index')->name('motors-pemilik');
        Volt::route('/owner/motors/create', 'pemilik.motors.create')->name('create-motors-pemilik');
        Volt::route('/owner/motors/detail/{id}', 'pemilik.motors.detail')->name('detail-motors-pemilik');
        Volt::route('/owner/motors/edit/{id}', 'pemilik.motors.edit')->name('edit-motors-pemilik');

        Volt::route('/owner/revenue', 'pemilik.revenue.index')->name('revenue-pemilik');
    });
    Route::middleware('role:penyewa')->group(function () {
        Volt::route('/dashboard', 'penyewa.dashboard.index')->name('dashboard-penyewa');

        Volt::route('/bookings', 'penyewa.motors.index')->name('motors-penyewa');
        Volt::route('/bookings/detail/{id}', 'penyewa.motors.detail')->name('detail-motors-penyewa');
        Volt::route('/bookings/edit/{id}', 'penyewa.motors.edit')->name('edit-motors-penyewa');
        Volt::route('/history', 'penyewa.bookings.history')->name('history-penyewa');

        Volt::route('/bookings/{id}', 'penyewa.bookings.index')->name('bookings-penyewa');

        Volt::route('/payments/{id}', 'penyewa.payments.index')->name('payments-penyewa');
        Volt::route('/payments/{id}', 'penyewa.payments.index')->name('payments-penyewa');
    });
    Volt::route('/', 'users.index');
});