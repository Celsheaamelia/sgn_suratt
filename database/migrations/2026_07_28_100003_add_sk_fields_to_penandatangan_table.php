<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Dibungkus hasTable supaya migration ini tetap aman dijalankan
        // walau nama tabel Penandatangan di project kamu ternyata beda.
        // Kalau ternyata beda nama, ganti 'penandatangan' di bawah ini saja.
        if (Schema::hasTable('penandatangan')) {
            Schema::table('penandatangan', function (Blueprint $table) {
                if (!Schema::hasColumn('penandatangan', 'no_sk')) {
                    $table->string('no_sk')->nullable(); // No. Surat Tugas/SK Direksi
                }
                if (!Schema::hasColumn('penandatangan', 'tanggal_sk')) {
                    $table->date('tanggal_sk')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('penandatangan')) {
            Schema::table('penandatangan', function (Blueprint $table) {
                if (Schema::hasColumn('penandatangan', 'no_sk')) {
                    $table->dropColumn('no_sk');
                }
                if (Schema::hasColumn('penandatangan', 'tanggal_sk')) {
                    $table->dropColumn('tanggal_sk');
                }
            });
        }
    }
};
