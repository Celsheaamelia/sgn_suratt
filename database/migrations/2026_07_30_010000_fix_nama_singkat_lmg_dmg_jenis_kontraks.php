<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Rapikan label singkat jenis kontrak KTR (12 bulan, luar/dalam masa giling)
 * dari "PKWT" jadi "PKWT DMG-LMG" -- biar konsisten sama istilah yang dipakai
 * di file Excel mail merge ("MAIL_MERGE_KONTRAK_PKWT_LMG-DMG_2026.xlsx") dan
 * gampang dibedain sama jenis PJJ ("PKWT DMG") di tabel Data Karyawan.
 *
 * Tidak mengubah struktur tabel, cuma benerin isi data yang sudah ada.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('jenis_kontraks')
            ->where('kode', 'PKWT-LMG')
            ->update(['nama_singkat' => 'PKWT DMG-LMG']);

        DB::table('jenis_kontraks')
            ->where('kode', 'PKWT-DMG')
            ->update(['nama_singkat' => 'PKWT DMG']);
    }

    public function down(): void
    {
        DB::table('jenis_kontraks')
            ->where('kode', 'PKWT-LMG')
            ->update(['nama_singkat' => 'PKWT']);
    }
};