<?php

namespace App\Console\Commands;

use App\Models\Karyawan;
use App\Models\Kontrak;
use App\Models\JenisKontrak;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

/**
 * Import data karyawan + riwayat kontrak dari file mail-merge Excel
 * (sheet "PKWT") ke database.
 *
 * Contoh pakai:
 *   php artisan kontrak:import "storage/app/import/MAIL_MERGE_KONTRAK_PKWT_LMG-DMG_2026.xlsx" --jenis=KTR
 *   php artisan kontrak:import "storage/app/import/MAIL_MERGE_KONTRAK_PKWT_DMG_2026.xlsx" --jenis=PJJ
 *
 * Aman dijalankan berulang kali (idempotent): karyawan di-upsert per NIK,
 * kontrak di-upsert per nomor_kontrak.
 *
 * CATATAN: disesuaikan dengan skema `karyawans` versi ramping (migration
 * "inti_tabel_karyawan") — kolom jabatan/bagian/tempat_lahir/tanggal_lahir/
 * status_karyawan SUDAH TIDAK ADA di tabel karyawans. Info jabatan & bagian
 * sekarang hanya disimpan di tabel kontraks (jabatan_kontrak, bagian_kontrak),
 * karena bisa beda tiap kontrak untuk karyawan yang sama.
 */
class ImportKontrakExcel extends Command
{
    protected $signature = 'kontrak:import
        {file : Path ke file .xlsx (relatif dari root project atau absolut)}
        {--jenis=KTR : Kode nomor jenis kontrak, KTR atau PJJ}
        {--sheet=PKWT : Nama sheet yang dibaca}
        {--user= : ID user pembuat record kontrak (default: user pertama di tabel users)}';

    protected $description = 'Import data karyawan & kontrak dari file mail-merge Excel (sheet PKWT)';

    private array $bulanIndo = [
        'januari' => 1, 'februari' => 2, 'maret' => 3, 'april' => 4,
        'mei' => 5, 'juni' => 6, 'juli' => 7, 'agustus' => 8,
        'september' => 9, 'oktober' => 10, 'november' => 11, 'desember' => 12,
    ];

    public function handle(): int
    {
        $path = base_path($this->argument('file'));
        if (!file_exists($path)) {
            $path = $this->argument('file'); // coba path absolut apa adanya
        }

        if (!file_exists($path)) {
            $this->error("File tidak ditemukan: {$path}");
            return self::FAILURE;
        }

        $jenisKode = strtoupper($this->option('jenis'));
        $jenisKontrak = JenisKontrak::where('kode_nomor', $jenisKode)->first();
        if (!$jenisKontrak) {
            $this->error("Jenis kontrak dengan kode_nomor '{$jenisKode}' tidak ditemukan. Jalankan seeder dulu: php artisan db:seed --class=JenisKontrakSeeder");
            return self::FAILURE;
        }

        $userId = $this->option('user') ?: (User::query()->orderBy('id')->value('id'));
        if (!$userId) {
            $this->error('Tidak ada user di database. Buat user dulu sebelum import.');
            return self::FAILURE;
        }

        $this->info("Membaca file: {$path}");
        $spreadsheet = IOFactory::load($path);
        $sheetName = $this->option('sheet');
        $sheet = $spreadsheet->getSheetByName($sheetName);

        if (!$sheet) {
            $this->error("Sheet '{$sheetName}' tidak ditemukan di file ini.");
            return self::FAILURE;
        }

        $highestRow = $sheet->getHighestDataRow();
        $highestColumnIndex = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

        // Baca header (baris 1) -> peta [nama header huruf besar => index kolom]
        $headers = [];
        for ($col = 1; $col <= $highestColumnIndex; $col++) {
            $colLetter = Coordinate::stringFromColumnIndex($col);
            $label = strtoupper(trim((string) $sheet->getCell($colLetter . '1')->getValue()));
            if ($label !== '') {
                $headers[$label] = $col;
            }
        }

        $countKaryawanBaru = 0;
        $countKaryawanUpdate = 0;
        $countKontrakBaru = 0;
        $countKontrakUpdate = 0;
        $skipped = [];

        $bar = $this->output->createProgressBar($highestRow - 1);
        $bar->start();

        for ($r = 2; $r <= $highestRow; $r++) {
            $bar->advance();

            $get = function (string $headerName) use ($sheet, $headers, $r) {
                if (!isset($headers[$headerName])) {
                    return null;
                }
                $colLetter = Coordinate::stringFromColumnIndex($headers[$headerName]);
                return $this->cellValue($sheet->getCell($colLetter . $r));
            };

            $nik = $this->normalizeNik($get('NIK KARYAWAN'));
            $nama = trim((string) ($get('NAMA') ?? ''));

            if (!$nik || !$nama) {
                continue; // baris kosong
            }

            $nomorKontrak = trim((string) ($get('NOMOR KONTRAK') ?? ''));
            if (!$nomorKontrak) {
                $skipped[] = "Baris {$r}: {$nama} — kolom NOMOR KONTRAK kosong, dilewati.";
                continue;
            }

            // ===== Upsert Karyawan (kolom identitas inti saja) =====
            $tempatTanggalLahir = $this->resolveTempatTanggalLahir($get);

            $karyawanPayload = array_filter([
                'no_ktp'               => $this->cleanText($get('NO KTP')),
                'nama'                 => $nama,
                'tempat_tanggal_lahir' => $tempatTanggalLahir,
                'jenis_kelamin'        => $this->normalizeJenisKelamin($get('JENIS KELAMIN')),
                'agama'                => $this->cleanText($get('AGAMA')),
                'status_perkawinan'    => $this->cleanText($get('STATUS PERKAWINAN')),
                'alamat'               => $this->cleanText($get('ALAMAT LENGKAP')),
            ], fn ($v) => $v !== null && $v !== '');

            $karyawan = Karyawan::where('nik', $nik)->first();
            if ($karyawan) {
                $karyawan->fill($karyawanPayload);
                $karyawan->save();
                $countKaryawanUpdate++;
            } else {
                $karyawan = Karyawan::create(array_merge(
                    ['nik' => $nik],
                    $karyawanPayload
                ));
                $countKaryawanBaru++;
            }

            // ===== Upsert Kontrak (jabatan/bagian/rincian pekerjaan hidup di sini) =====
            $tanggalKontrak = $this->extractTanggalFromNomor($nomorKontrak)
                ?? $this->parseIndonesianDate($get('PERIODE KONTRAK DARI'))
                ?? now();

            $tanggalMulai = $this->parseIndonesianDate($get('PERIODE KONTRAK DARI')) ?? $tanggalKontrak;
            $tanggalSelesai = $this->parseIndonesianDate($get('PERIODE KONTRAK SAMPAI'));

            $statusAsal = $this->cleanText($get('STATUS'));

            $kontrakPayload = [
                'tanggal'             => $tanggalKontrak->toDateString(),
                'karyawan_id'         => $karyawan->id,
                'jenis_kontrak_id'    => $jenisKontrak->id,
                'penandatangan_id'    => null,
                'tanggal_mulai'       => $tanggalMulai->toDateString(),
                'tanggal_selesai'     => $tanggalSelesai?->toDateString(),
                'jabatan_kontrak'     => $this->cleanText($get('JABATAN')),
                'bagian_kontrak'      => $this->cleanText($get('BAGIAN')),
                'rincian_pekerjaan_1' => $this->cleanText($get('RINCIAN PEKERJAAN 1')),
                'rincian_pekerjaan_2' => $this->cleanText($get('RINCIAN PEKERJAAN 2')),
                'rincian_pekerjaan_3' => $this->cleanText($get('RINCIAN PEKERJAAN 3')),
                'catatan'             => $statusAsal ? "Diimpor dari mail merge. Status asal: {$statusAsal}" : 'Diimpor dari mail merge.',
                'status'              => 'Aktif',
                'user_id'             => $userId,
            ];

            $existingKontrak = Kontrak::where('nomor_kontrak', $nomorKontrak)->first();
            if ($existingKontrak) {
                $existingKontrak->update($kontrakPayload);
                $countKontrakUpdate++;
            } else {
                Kontrak::create(array_merge(['nomor_kontrak' => $nomorKontrak], $kontrakPayload));
                $countKontrakBaru++;
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("Karyawan baru      : {$countKaryawanBaru}");
        $this->info("Karyawan diupdate  : {$countKaryawanUpdate}");
        $this->info("Kontrak baru       : {$countKontrakBaru}");
        $this->info("Kontrak diupdate   : {$countKontrakUpdate}");

        if ($skipped) {
            $this->newLine();
            $this->warn('Baris yang dilewati (' . count($skipped) . '):');
            foreach ($skipped as $s) {
                $this->line("  - {$s}");
            }
        }

        return self::SUCCESS;
    }

    private function cellValue($cell)
    {
        if ($cell === null) {
            return null;
        }

        $value = $cell->getCalculatedValue();

        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value) && ExcelDate::isDateTime($cell)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value));
            } catch (\Throwable $e) {
                // lanjut sebagai teks biasa
            }
        }

        return trim((string) $value);
    }

    private function cleanText($value): ?string
    {
        if ($value === null) {
            return null;
        }
        if ($value instanceof Carbon) {
            return $value->toDateString();
        }
        $value = trim((string) $value);
        if ($value === '' || $value === '-') {
            return null;
        }
        return $value;
    }

    private function normalizeNik($value): ?string
    {
        if ($value === null) {
            return null;
        }
        if (is_numeric($value) && !str_contains((string) $value, '.')) {
            return (string) $value;
        }
        return trim((string) $value) ?: null;
    }

    private function normalizeJenisKelamin($value): ?string
    {
        if (!$value) {
            return null;
        }
        $v = strtolower((string) $value);
        if (str_contains($v, 'laki')) {
            return 'Laki-laki';
        }
        if (str_contains($v, 'perempuan') || str_contains($v, 'wanita')) {
            return 'Perempuan';
        }
        return null;
    }

    /**
     * Ambil string "tempat, tanggal lahir" apa adanya, sesuai kolom yang
     * tersedia di file. Prioritas: kolom gabungan "TEMPAT TANGGAL LAHIR"
     * (format aslinya dipakai apa adanya, sama seperti karyawan_import.csv).
     * Kalau tidak ada, coba gabungkan dari "TEMPAT LAHIR" + "TANGGAL LAHIR".
     */
    private function resolveTempatTanggalLahir(callable $get): ?string
    {
        $gabungan = $this->cleanText($get('TEMPAT TANGGAL LAHIR'));
        if ($gabungan) {
            return $gabungan;
        }

        $tempatLahir = $this->cleanText($get('TEMPAT LAHIR'));
        $tanggalLahirRaw = $get('TANGGAL LAHIR');

        if ($tempatLahir && $tanggalLahirRaw) {
            $tanggalText = $tanggalLahirRaw instanceof Carbon
                ? $tanggalLahirRaw->translatedFormat('d F Y')
                : (string) $tanggalLahirRaw;

            return "{$tempatLahir} / {$tanggalText}";
        }

        return $tempatLahir;
    }

    /**
     * Parse tanggal berbahasa Indonesia ("8 Mei 2026") atau format DD/MM/YYYY.
     * Kalau tidak berhasil dikenali (misal teks "Berakhirnya Giling"), null.
     */
    private function parseIndonesianDate($value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }
        if (!$value || !is_string($value)) {
            return null;
        }

        $value = trim($value);

        if (preg_match('/(\d{1,2})\s+([A-Za-z]+)\s+(\d{4})/', $value, $m)) {
            $bulan = $this->bulanIndo[strtolower($m[2])] ?? null;
            if ($bulan) {
                try {
                    return Carbon::createFromDate((int) $m[3], $bulan, (int) $m[1])->startOfDay();
                } catch (\Throwable $e) {
                    return null;
                }
            }
        }

        if (preg_match('/(\d{1,2})\/(\d{1,2})\/(\d{4})/', $value, $m)) {
            try {
                return Carbon::createFromDate((int) $m[3], (int) $m[2], (int) $m[1])->startOfDay();
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }

    /**
     * Ambil tanggal dari format nomor kontrak: PREFIX-KODE/YYYYMMDD.URUT
     */
    private function extractTanggalFromNomor(string $nomorKontrak): ?Carbon
    {
        if (preg_match('/(\d{8})\.\d+$/', $nomorKontrak, $m)) {
            try {
                return Carbon::createFromFormat('Ymd', $m[1])->startOfDay();
            } catch (\Throwable $e) {
                return null;
            }
        }
        return null;
    }
}