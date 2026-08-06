<?php

namespace Database\Seeders;

use App\Models\Karyawan;
use App\Models\Kontrak;
use App\Models\JenisKontrak;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Import data kontrak (+ lengkapi identitas karyawan) dari
 * database/seeders/data/kontrak_import.csv.
 *
 * CSV ini sudah diolah dari 2 file mail-merge:
 *   - MAIL_MERGE_KONTRAK_PKWT_DMG_2026.xlsx       (jenis_kode PJJ)
 *   - MAIL_MERGE_KONTRAK_PKWT_LMG-DMG_2026.xlsx   (jenis_kode KTR)
 * Tanggal-tanggalnya (tanggal_kontrak, tanggal_mulai, tanggal_selesai)
 * SUDAH di-parse ke format YYYY-MM-DD, jadi seeder ini tinggal insert,
 * tidak perlu baca file .xlsx lagi.
 *
 * Aman dijalankan berkali-kali: karyawan di-upsert per NIK,
 * kontrak di-upsert per nomor_kontrak.
 *
 * Syarat sebelum jalan:
 *   php artisan db:seed --class=JenisKontrakSeeder   (kalau belum)
 *   minimal ada 1 user di tabel users
 */
class KontrakImportSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/kontrak_import.csv');

        if (!file_exists($path)) {
            $this->command?->warn("File tidak ditemukan: {$path}");
            return;
        }

        $userId = User::query()->orderBy('id')->value('id');
        if (!$userId) {
            $this->command?->warn('Tidak ada user di database. Buat user dulu sebelum import kontrak.');
            return;
        }

        $jenisKontrakCache = [];
        $getJenisKontrak = function (string $kode) use (&$jenisKontrakCache) {
            if (!isset($jenisKontrakCache[$kode])) {
                $jenisKontrakCache[$kode] = JenisKontrak::where('kode_nomor', $kode)->first();
            }
            return $jenisKontrakCache[$kode];
        };

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);

        $countKaryawan = 0;
        $countKontrak = 0;
        $skipped = [];
        $baris = 1;

        while (($row = fgetcsv($handle)) !== false) {
            $baris++;

            if (count($row) !== count($header)) {
                $skipped[] = "Baris {$baris}: jumlah kolom tidak cocok, dilewati.";
                continue;
            }

            $data = array_combine($header, $row);

            if (empty($data['nik']) || empty($data['nama']) || empty($data['nomor_kontrak'])) {
                $skipped[] = "Baris {$baris}: NIK/nama/nomor_kontrak kosong, dilewati.";
                continue;
            }

            $jenisKontrak = $getJenisKontrak($data['jenis_kode']);
            if (!$jenisKontrak) {
                $skipped[] = "Baris {$baris}: jenis kontrak '{$data['jenis_kode']}' tidak ditemukan (jalankan JenisKontrakSeeder dulu), dilewati.";
                continue;
            }

            // ===== Upsert Karyawan =====
            $karyawan = Karyawan::updateOrCreate(
                ['nik' => trim($data['nik'])],
                array_filter([
                    'no_ktp'               => $data['no_ktp'] ?: null,
                    'nama'                 => $data['nama'],
                    'tempat_tanggal_lahir' => $data['tempat_tanggal_lahir'] ?: null,
                    'jenis_kelamin'        => $data['jenis_kelamin'] ?: null,
                    'agama'                => $data['agama'] ?: null,
                    'status_perkawinan'    => $data['status_perkawinan'] ?: null,
                    'alamat'               => $data['alamat'] ?: null,
                ], fn ($v) => $v !== null)
            );
            $countKaryawan++;

            // ===== Upsert Kontrak =====
            $tanggalKontrak = $data['tanggal_kontrak'] ?: $data['tanggal_mulai'] ?: now()->toDateString();
            $tanggalMulai = $data['tanggal_mulai'] ?: $tanggalKontrak;

            Kontrak::updateOrCreate(
                ['nomor_kontrak' => $data['nomor_kontrak']],
                [
                    'tanggal'             => $tanggalKontrak,
                    'karyawan_id'         => $karyawan->id,
                    'jenis_kontrak_id'    => $jenisKontrak->id,
                    'penandatangan_id'    => null,
                    'tanggal_mulai'       => $tanggalMulai,
                    'tanggal_selesai'     => $data['tanggal_selesai'] ?: null,
                    'jabatan_kontrak'     => $data['jabatan_kontrak'] ?: null,
                    'bagian_kontrak'      => $data['bagian_kontrak'] ?: null,
                    'rincian_pekerjaan_1' => $data['rincian_pekerjaan_1'] ?: null,
                    'rincian_pekerjaan_2' => $data['rincian_pekerjaan_2'] ?: null,
                    'rincian_pekerjaan_3' => $data['rincian_pekerjaan_3'] ?: null,
                    'catatan'             => $data['status_asal'] ? "Diimpor dari mail merge. Status asal: {$data['status_asal']}" : 'Diimpor dari mail merge.',
                    'status'              => 'Aktif',
                    'user_id'             => $userId,
                ]
            );
            $countKontrak++;
        }

        fclose($handle);

        $this->command?->info("Karyawan diupsert : {$countKaryawan}");
        $this->command?->info("Kontrak diupsert  : {$countKontrak}");

        if ($skipped) {
            $this->command?->warn('Baris yang dilewati (' . count($skipped) . '):');
            foreach ($skipped as $s) {
                $this->command?->line("  - {$s}");
            }
        }
    }
}
