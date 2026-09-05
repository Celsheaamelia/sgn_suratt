<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom ini dipakai oleh command patroli:eskalasi-temuan (lihat
     * app/Console/Commands/EskalasiTemuanPatroli.php) untuk melacak:
     * - sudah sampai level eskalasi berapa suatu temuan (0 = belum, 1/2/3 = makin mendesak)
     * - kapan notifikasi eskalasi terakhir dikirim, supaya tidak spam di setiap jadwal cron.
     */
    public function up(): void
    {
        Schema::table('patrol_scans', function (Blueprint $table) {
            $table->unsignedTinyInteger('eskalasi_level')->default(0)->after('ditangani_at');
            $table->timestamp('eskalasi_terakhir_at')->nullable()->after('eskalasi_level');
        });
    }

    public function down(): void
    {
        Schema::table('patrol_scans', function (Blueprint $table) {
            $table->dropColumn(['eskalasi_level', 'eskalasi_terakhir_at']);
        });
    }
};
