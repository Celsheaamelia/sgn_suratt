<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Jaga-jaga: kalau sudah ada data duplikat document_no sebelum migrasi ini,
        // migrasi unique index akan gagal. Kita cek dulu supaya errornya jelas.
        $duplicates = DB::table('arsip_kasbon')
            ->select('document_no')
            ->whereNotNull('document_no')
            ->groupBy('document_no')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('document_no');

        if ($duplicates->isNotEmpty()) {
            throw new \Exception(
                'Tidak bisa menambahkan unique index: sudah ada document_no duplikat di data lama: '
                . $duplicates->implode(', ')
                . '. Hapus/gabungkan data duplikat itu dulu sebelum menjalankan migrasi ini.'
            );
        }

        Schema::table('arsip_kasbon', function (Blueprint $table) {
            $table->unique('document_no', 'arsip_kasbon_document_no_unique');
        });
    }

    public function down(): void
    {
        Schema::table('arsip_kasbon', function (Blueprint $table) {
            $table->dropUnique('arsip_kasbon_document_no_unique');
        });
    }
};