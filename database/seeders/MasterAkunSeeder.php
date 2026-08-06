<?php

namespace Database\Seeders;

use App\Models\MasterAkun;
use Illuminate\Database\Seeder;

class MasterAkunSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            ['no_akun' => '51100107', 'deskripsi' => 'Biaya Pemeliharaan Peralatan Dan Inventaris'],
            ['no_akun' => '21030113', 'deskripsi' => 'Hutang Lainnya Karyawan'],
        ];

        foreach ($data as $row) {
            MasterAkun::updateOrCreate(['no_akun' => $row['no_akun']], $row);
        }
    }
}
