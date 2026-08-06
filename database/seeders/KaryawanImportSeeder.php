<?php

namespace Database\Seeders;

use App\Models\Karyawan;
use Illuminate\Database\Seeder;

/**
 * Import data identitas karyawan dari database/seeders/data/karyawan_import.csv.
 * 914 baris unik berdasarkan NIK Karyawan.
 *
 * Aman dijalankan berkali-kali - pakai updateOrCreate berdasarkan NIK,
 * jadi kalau CSV-nya diupdate lagi nanti tinggal seed ulang.
 */
class KaryawanImportSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/karyawan_import.csv');

        if (!file_exists($path)) {
            $this->command?->warn("File tidak ditemukan: {$path}");
            return;
        }

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);

        $count = 0;

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) !== count($header)) {
                continue; // baris rusak/kosong, skip
            }

            $data = array_combine($header, $row);

            if (empty($data['nik']) || empty($data['nama'])) {
                continue;
            }

            Karyawan::updateOrCreate(
                ['nik' => trim($data['nik'])],
                [
                    'no_ktp'                => $data['no_ktp'] ?: null,
                    'nama'                  => $data['nama'],
                    'tempat_tanggal_lahir'  => $data['tempat_tanggal_lahir'] ?: null,
                    'jenis_kelamin'         => $data['jenis_kelamin'] ?: null,
                    'agama'                 => $data['agama'] ?: null,
                    'status_perkawinan'     => $data['status_perkawinan'] ?: null,
                    'alamat'                => $data['alamat'] ?: null,
                ]
            );

            $count++;
        }

        fclose($handle);

        $this->command?->info("Karyawan berhasil diimport: {$count} baris.");
    }
}
