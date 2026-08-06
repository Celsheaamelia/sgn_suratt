<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom "numerator" -- nomor urut cetak/cap mesin di pojok atas form SPP
     * (contoh: 2407009), BEDA dengan document_no (nomor dokumen SAP, contoh: 1900031757).
     * Dua-duanya sama-sama muncul di kertas fisik, makanya dipisah field-nya
     * supaya gak ketuker pas OCR atau pas dicari lagi di arsip.
     */
    public function up(): void
    {
        Schema::table('arsip_kasbon', function (Blueprint $table) {
            $table->string('numerator', 50)->nullable()->after('document_no');
        });
    }

    public function down(): void
    {
        Schema::table('arsip_kasbon', function (Blueprint $table) {
            $table->dropColumn('numerator');
        });
    }
};