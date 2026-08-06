<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jenis_kontraks', function (Blueprint $table) {
            if (!Schema::hasColumn('jenis_kontraks', 'nama_singkat')) {
                // Label pendek buat ditampilkan di dropdown & preview,
                // sesuai istilah yang biasa dipakai di Excel: "PKWT" / "PKWT DMG"
                $table->string('nama_singkat', 30)->nullable()->after('nama_jenis');
            }
            if (!Schema::hasColumn('jenis_kontraks', 'gaji_pokok_default')) {
                // Nominal gaji pokok sudah tetap/standar sesuai draft kontrak,
                // jadi tidak perlu diinput manual tiap bikin kontrak baru.
                $table->decimal('gaji_pokok_default', 15, 2)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('jenis_kontraks', function (Blueprint $table) {
            foreach (['nama_singkat', 'gaji_pokok_default'] as $col) {
                if (Schema::hasColumn('jenis_kontraks', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
