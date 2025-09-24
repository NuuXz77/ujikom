<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            // Admin user
            [
                'kode_user' => 'ADM001',
                'nama' => 'Admin User',
                'email' => 'admin@a.com',
                'password' => Hash::make('password123'),
                'no_telp' => '081234567890',
                'role' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Pemilik (owner) user
            [
                'kode_user' => 'OWN002',
                'nama' => 'Pemilik Motor',
                'email' => 'pemilik@a.com',
                'password' => Hash::make('password123'),
                'no_telp' => '089876543210',
                'role' => 'pemilik',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Penyewa (renter) user
            [
                'kode_user' => 'REN001',
                'nama' => 'Penyewa Motor',
                'email' => 'penyewa@a.com',
                'password' => Hash::make('password123'),
                'no_telp' => '085555555555',
                'role' => 'penyewa',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}