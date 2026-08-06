<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kolom 'nama' TIDAK PERNAH dibuat di migration manapun untuk tabel
 * penandatangan (create_penandatangan_table cuma punya kode + jabatan).
 * Akibatnya $ttd->nama di KontrakController::generateDocument() selalu
 * null, dan dokumen hasil generate menampilkan jabatan ("General Manager")
 * sebagai pengganti nama orang ("Agus Amanda").
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('penandatangan') && !Schema::hasColumn('penandatangan', 'nama')) {
            Schema::table('penandatangan', function (Blueprint $table) {
                $table->string('nama', 150)->nullable()->after('jabatan');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('penandatangan', 'nama')) {
            Schema::table('penandatangan', function (Blueprint $table) {
                $table->dropColumn('nama');
            });
        }
    }
};
