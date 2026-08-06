<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arsip_kasbon', function (Blueprint $table) {
            $table->dropUnique('arsip_kasbon_document_no_unique');
        });

        DB::statement('
            ALTER TABLE arsip_kasbon
            ADD COLUMN tahun_transaksi INT
            GENERATED ALWAYS AS (YEAR(tanggal_transaksi)) STORED
        ');

        Schema::table('arsip_kasbon', function (Blueprint $table) {
            $table->unique(['document_no', 'tahun_transaksi'], 'arsip_kasbon_docno_tahun_unique');
        });
    }

    public function down(): void
    {
        Schema::table('arsip_kasbon', function (Blueprint $table) {
            $table->dropUnique('arsip_kasbon_docno_tahun_unique');
        });

        DB::statement('ALTER TABLE arsip_kasbon DROP COLUMN tahun_transaksi');

        Schema::table('arsip_kasbon', function (Blueprint $table) {
            $table->unique('document_no', 'arsip_kasbon_document_no_unique');
        });
    }
};
