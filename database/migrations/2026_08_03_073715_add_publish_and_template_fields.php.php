<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('kontraks', function (Blueprint $table) {
            if (!Schema::hasColumn('kontraks', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('kontraks', 'template_id')) {
                $table->foreignId('template_id')->nullable()->after('jenis_kontrak_id')
                    ->constrained('templates')->nullOnDelete();
            }
        });

        // Kolom no_sk & tanggal_sk TIDAK dibuat di sini - sudah ada dari
        // migration 2026_07_28_100003_add_sk_fields_to_penandatangan_table.
        // Tabelnya juga singular ('penandatangan'), bukan 'penandatangans'.

        // Isi data SK yang sebelumnya hardcode di controller, untuk baris
        // yang kolom SK-nya masih kosong, supaya kontrak yang sudah ada
        // tetap menghasilkan nilai yang sama persis kalau di-generate ulang.
        // SESUAIKAN nilai ini kalau tiap penandatangan punya SK yang
        // beda-beda - ini cuma nilai default sementara.
        DB::table('penandatangan')
            ->whereNull('no_sk')
            ->update([
                'no_sk'      => 'BD01-KOLE-SKP/20260708.009',
                'tanggal_sk' => '2026-07-08',
            ]);

        // Kontrak yang sebelumnya sudah punya generated_file_path (berarti
        // sudah pernah dipakai/dianggap final sebelum fitur publish ini ada)
        // langsung ditandai published, supaya download-nya tidak tiba-tiba
        // ke-lock untuk data lama.
        DB::table('kontraks')
            ->whereNotNull('generated_file_path')
            ->update(['published_at' => now()]);
    }

    public function down(): void
    {
        Schema::table('kontraks', function (Blueprint $table) {
            if (Schema::hasColumn('kontraks', 'template_id')) {
                $table->dropConstrainedForeignId('template_id');
            }
            if (Schema::hasColumn('kontraks', 'published_at')) {
                $table->dropColumn('published_at');
            }
        });
    }
};
