<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('riwayatsurat', function (Blueprint $table) {
            DB::statement("ALTER TABLE riwayatsurat MODIFY status ENUM('Terupload', 'Belum Terupload', 'Direservasi') NOT NULL DEFAULT 'Belum Terupload'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('riwayatsurat', function (Blueprint $table) {
            DB::statement("ALTER TABLE riwayatsurat MODIFY status ENUM('Terupload', 'Belum Terupload') NOT NULL DEFAULT 'Belum Terupload'");
        });
    }
};
