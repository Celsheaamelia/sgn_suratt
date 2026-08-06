<?php

namespace App\Support;

use Carbon\Carbon;

/**
 * Kalimat pembuka kontrak butuh format "Pada hari ini, Jumat tanggal Delapan
 * bulan Mei dua ribu dua puluh enam (08-05-2026)" — ini generator otomatisnya
 * supaya nggak perlu isi manual di Excel kayak sebelumnya (kolom PADA HARI INI
 * & TANGGAL KONTRAK TERBILANG dulu diisi manual satu-satu).
 */
class IndonesianDate
{
    private const HARI = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
    ];

    private const BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    private const ANGKA = [
        1 => 'Satu', 2 => 'Dua', 3 => 'Tiga', 4 => 'Empat', 5 => 'Lima', 6 => 'Enam', 7 => 'Tujuh',
        8 => 'Delapan', 9 => 'Sembilan', 10 => 'Sepuluh', 11 => 'Sebelas', 12 => 'Dua Belas',
        13 => 'Tiga Belas', 14 => 'Empat Belas', 15 => 'Lima Belas', 16 => 'Enam Belas',
        17 => 'Tujuh Belas', 18 => 'Delapan Belas', 19 => 'Sembilan Belas', 20 => 'Dua Puluh',
        21 => 'Dua Puluh Satu', 22 => 'Dua Puluh Dua', 23 => 'Dua Puluh Tiga', 24 => 'Dua Puluh Empat',
        25 => 'Dua Puluh Lima', 26 => 'Dua Puluh Enam', 27 => 'Dua Puluh Tujuh', 28 => 'Dua Puluh Delapan',
        29 => 'Dua Puluh Sembilan', 30 => 'Tiga Puluh', 31 => 'Tiga Puluh Satu',
    ];

    public static function namaHari(Carbon $tanggal): string
    {
        return self::HARI[$tanggal->format('l')] ?? $tanggal->format('l');
    }

    public static function namaBulan(Carbon $tanggal): string
    {
        return self::BULAN[(int) $tanggal->format('n')] ?? $tanggal->format('F');
    }

    public static function tanggalTerbilang(Carbon $tanggal): string
    {
        return self::ANGKA[(int) $tanggal->format('j')] ?? (string) $tanggal->format('j');
    }

    /**
     * Angka rupiah -> terbilang (dipakai untuk gaji: "Rp. 2.578.320,- (dua
     * juta lima ratus tujuh puluh delapan ribu tiga ratus dua puluh rupiah)").
     * Cukup untuk nominal gaji (puluhan ribu s/d milyaran), tidak untuk desimal.
     */
    public static function angkaTerbilang(int $angka): string
    {
        $angka = abs($angka);

        if ($angka < 12) {
            return self::satuanTerbilang($angka);
        }
        if ($angka < 20) {
            return self::satuanTerbilang($angka - 10) . ' Belas';
        }
        if ($angka < 100) {
            $sisa = $angka % 10;
            return self::satuanTerbilang(intdiv($angka, 10)) . ' Puluh' . ($sisa ? ' ' . self::satuanTerbilang($sisa) : '');
        }
        if ($angka < 200) {
            $sisa = $angka - 100;
            return 'Seratus' . ($sisa ? ' ' . self::angkaTerbilang($sisa) : '');
        }
        if ($angka < 1000) {
            $sisa = $angka % 100;
            return self::satuanTerbilang(intdiv($angka, 100)) . ' Ratus' . ($sisa ? ' ' . self::angkaTerbilang($sisa) : '');
        }
        if ($angka < 2000) {
            $sisa = $angka - 1000;
            return 'Seribu' . ($sisa ? ' ' . self::angkaTerbilang($sisa) : '');
        }
        if ($angka < 1000000) {
            $sisa = $angka % 1000;
            return self::angkaTerbilang(intdiv($angka, 1000)) . ' Ribu' . ($sisa ? ' ' . self::angkaTerbilang($sisa) : '');
        }
        if ($angka < 1000000000) {
            $sisa = $angka % 1000000;
            return self::angkaTerbilang(intdiv($angka, 1000000)) . ' Juta' . ($sisa ? ' ' . self::angkaTerbilang($sisa) : '');
        }

        $sisa = $angka % 1000000000;
        return self::angkaTerbilang(intdiv($angka, 1000000000)) . ' Milyar' . ($sisa ? ' ' . self::angkaTerbilang($sisa) : '');
    }

    private static function satuanTerbilang(int $n): string
    {
        $words = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        return $words[$n] ?? (string) $n;
    }

    public static function rupiahTerbilang(int $angka): string
    {
        return self::angkaTerbilang($angka) . ' Rupiah';
    }
}
