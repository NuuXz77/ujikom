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
        Volt::route('/admin/motors/detail/{id}', 'admin.motors.detail')->name('detail-motors-admin');
        Volt::route('/admin/motors/edit/{id}', 'admin.motors.edit')->name('edit-motors-admin');

        Volt::route('/admin/bookings', 'admin.bookings.index')->name('bookings-admin');

        Volt::route('/admin/users', 'admin.users.index')->name('users-admin');

        Volt::route('/admin/revenue', 'admin.revenue.index')->name('revenue-admin');

        Volt::route('/admin/history', 'admin.history.index')->name('history-admin');
    });
    Route::middleware('role:pemilik')->group(function () {
        Volt::route('/owner/dashboard', 'pemilik.dashboard.index')->name('dashboard-pemilik');

        Volt::route('/owner/motors', 'pemilik.motors.index')->name('motors-pemilik');
        Volt::route('/owner/motors/create', 'pemilik.motors.create')->name('create-motors-pemilik');

        Volt::route('/owner/revenue', 'pemilik.revenue.index')->name('revenue-pemilik');
    });
    Route::middleware('role:penyewa')->group(function () {
        Volt::route('/dashboard', 'penyewa.dashboard.index')->name('dashboard-penyewa');

        Volt::route('/bookings', 'penyewa.motors.index')->name('motors-penyewa');

        Volt::route('/bookings/{id}', 'penyewa.bookings.index')->name('bookings-penyewa');

        Volt::route('/payments/{id}', 'penyewa.payments.index')->name('payments-penyewa');
    });
});