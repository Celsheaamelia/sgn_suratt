<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jenis_kontraks', function (Blueprint $table) {
            if (!Schema::hasColumn('jenis_kontraks', 'kode_nomor')) {
                // Kode klasifikasi buat rangkaian nomor kontrak, TERPISAH dari
                // 'kode' biasa. Ini yang bikin PJJ (masa giling) dan KTR (12
                // bulan / luar masa giling) punya urutan nomor sendiri-sendiri
                // walau tanggalnya sama. Contoh: SG26-PERSE-PJJ/20260508.001
                $table->string('kode_nomor', 20)->nullable()->after('kode');
            }
            if (!Schema::hasColumn('jenis_kontraks', 'masa_giling')) {
                // true = tanggal_selesai kontrak dihitung sampai "Berakhirnya
                // Masa Giling" (bukan tanggal tetap), dipakai di generateDocument().
                $table->boolean('masa_giling')->default(false);
            }
            if (!Schema::hasColumn('jenis_kontraks', 'template_file')) {
                // Path relatif ke storage/app/, contoh:
                // templates/kontrak/template_pkwt_dmg_pjj.docx
                $table->string('template_file')->nullable();
            }
            if (!Schema::hasColumn('jenis_kontraks', 'deskripsi')) {
                $table->text('deskripsi')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('jenis_kontraks', function (Blueprint $table) {
            foreach (['kode_nomor', 'masa_giling', 'template_file'] as $col) {
                if (Schema::hasColumn('jenis_kontraks', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
