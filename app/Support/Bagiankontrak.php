<?php

namespace App\Support;

/**
 * Daftar tetap "Bagian / Departemen" untuk kontrak.
 *
 * Dipakai bareng-bareng di form buat kontrak (dropdown input), filter
 * Riwayat Kontrak, dan fitur unduh dokumen per bagian - supaya nilainya
 * selalu konsisten (nggak ada typo / variasi teks bebas lagi).
 */
class BagianKontrak
{
    public const OPTIONS = [
        'Keuangan & Umum',
        'Pengolahan',
        'Quality Assurance',
        'Instalasi',
        'Tanaman',
    ];
}