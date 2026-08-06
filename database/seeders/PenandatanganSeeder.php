<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PenandatanganSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('penandatangan')->upsert([
            [
                'kode' => 'SG26',
                'jabatan' => 'General Manager',
                'nama'     => 'Agus Amanda',
            ],
            [
                'kode' => 'SG26F',
                'jabatan' => 'Manager',
                'nama' => null,
            ],
            [
                'kode' => 'ASMAN',
                'jabatan' => 'Asistan Manager',
                'nama' => null,
            ],
            ],
            ['kode'], // kolom unik
            ['jabatan', 'nama'] // kolom yang di-update jika kode sudah ada
        );
    }
}
