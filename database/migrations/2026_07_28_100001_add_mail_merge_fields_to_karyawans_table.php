<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            if (!Schema::hasColumn('karyawans', 'no_ktp')) {
                $table->string('no_ktp', 20)->nullable()->after('nik');
            }
            if (!Schema::hasColumn('karyawans', 'rincian_pekerjaan_1')) {
                $table->string('rincian_pekerjaan_1')->nullable();
                $table->string('rincian_pekerjaan_2')->nullable();
                $table->string('rincian_pekerjaan_3')->nullable();
            }
            if (!Schema::hasColumn('karyawans', 'status_kepegawaian')) {
                $table->string('status_kepegawaian')->nullable(); // cth: OS, PKWT, PKWT DMG, PKWT 12 BLN
            }
            if (!Schema::hasColumn('karyawans', 'nilai_grade')) {
                $table->unsignedInteger('nilai_grade')->nullable(); // grade/nilai untuk bantuan sosial dsb
            }
            if (!Schema::hasColumn('karyawans', 'jenis_kelamin')) {
                $table->string('jenis_kelamin', 20)->nullable();
            }
            if (!Schema::hasColumn('karyawans', 'agama')) {
                $table->string('agama', 30)->nullable();
            }
            if (!Schema::hasColumn('karyawans', 'status_perkawinan')) {
                $table->string('status_perkawinan', 30)->nullable();
            }
        });

        // "tempat_tanggal_lahir" di data asli datang sebagai satu string gabungan
        // ("Lumajang / 29 November 1971"), bukan 2 kolom terpisah yang gampang
        // dipisah otomatis (formatnya suka beda-beda: "/", ",", ada yang tanpa
        // spasi dsb). Supaya data asli tidak perlu diparsing ulang / berisiko
        // salah potong, kita simpan sebagai satu kolom teks apa adanya.
        Schema::table('karyawans', function (Blueprint $table) {
            if (!Schema::hasColumn('karyawans', 'tempat_tanggal_lahir')) {
                $table->string('tempat_tanggal_lahir')->nullable()->after('tanggal_lahir');
            }
        });
    }

    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->dropColumn([
                'no_ktp', 'rincian_pekerjaan_1', 'rincian_pekerjaan_2', 'rincian_pekerjaan_3',
                'status_kepegawaian', 'nilai_grade', 'jenis_kelamin', 'agama',
                'status_perkawinan', 'tempat_tanggal_lahir',
            ]);
        });
    }
};
