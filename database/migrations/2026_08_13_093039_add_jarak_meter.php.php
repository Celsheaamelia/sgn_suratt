<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jarak (meter) antara lokasi GPS petugas saat scan dengan koordinat checkpoint terdaftar.
     * Dipakai untuk audit & indikator kewajaran lokasi, bukan pemblokir keras (GPS di pabrik
     * kadang kurang akurat), tapi tetap tercatat supaya supervisor bisa cek kalau curiga.
     */
    public function up(): void
    {
        Schema::table('patrol_scans', function (Blueprint $table) {
            if (! Schema::hasColumn('patrol_scans', 'jarak_meter')) {
                $table->unsignedInteger('jarak_meter')->nullable()->after('longitude');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patrol_scans', function (Blueprint $table) {
            if (Schema::hasColumn('patrol_scans', 'jarak_meter')) {
                $table->dropColumn('jarak_meter');
            }
        });
    }
};
