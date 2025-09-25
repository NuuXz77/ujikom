<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MotorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('motors')->insert([
            [
                'owner_id' => 2,
                'merk' => 'Honda Beat',
                'tipe_cc' => '110cc',
                'no_plat' => 'B 1234 ABC',
                'status' => 'tersedia',
                'photo' => null,
                'dokumen_kepemilikan' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'owner_id' => 2,
                'merk' => 'Yamaha NMAX',
                'tipe_cc' => '155cc',
                'no_plat' => 'B 2345 BCD',
                'status' => 'tersedia',
                'photo' => null,
                'dokumen_kepemilikan' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'owner_id' => 2,
                'merk' => 'Suzuki Satria',
                'tipe_cc' => '150cc',
                'no_plat' => 'B 3456 CDE',
                'status' => 'tersedia',
                'photo' => null,
                'dokumen_kepemilikan' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'owner_id' => 2,
                'merk' => 'Honda Vario',
                'tipe_cc' => '125cc',
                'no_plat' => 'B 4567 DEF',
                'status' => 'tersedia',
                'photo' => null,
                'dokumen_kepemilikan' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'owner_id' => 2,
                'merk' => 'Yamaha Aerox',
                'tipe_cc' => '155cc',
                'no_plat' => 'B 5678 EFG',
                'status' => 'tersedia',
                'photo' => null,
                'dokumen_kepemilikan' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'owner_id' => 2,
                'merk' => 'Kawasaki Ninja',
                'tipe_cc' => '250cc',
                'no_plat' => 'B 6789 FGH',
                'status' => 'tersedia',
                'photo' => null,
                'dokumen_kepemilikan' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'owner_id' => 2,
                'merk' => 'Honda Scoopy',
                'tipe_cc' => '110cc',
                'no_plat' => 'B 7890 GHI',
                'status' => 'tersedia',
                'photo' => null,
                'dokumen_kepemilikan' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'owner_id' => 2,
                'merk' => 'Yamaha Mio',
                'tipe_cc' => '125cc',
                'no_plat' => 'B 8901 HIJ',
                'status' => 'tersedia',
                'photo' => null,
                'dokumen_kepemilikan' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'owner_id' => 2,
                'merk' => 'Honda PCX',
                'tipe_cc' => '160cc',
                'no_plat' => 'B 9012 IJK',
                'status' => 'tersedia',
                'photo' => null,
                'dokumen_kepemilikan' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'owner_id' => 2,
                'merk' => 'Vespa Sprint',
                'tipe_cc' => '150cc',
                'no_plat' => 'B 0123 JKL',
                'status' => 'tersedia',
                'photo' => null,
                'dokumen_kepemilikan' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
