<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ramping-kan tabel karyawans jadi cuma data identitas inti (sesuai blok
 * "PIHAK KEDUA" di template PKWT):
 *   Nama, No. KTP, Tempat/Tanggal Lahir, Jenis Kelamin, Agama,
 *   Status Perkawinan, Alamat Lengkap (+ NIK sebagai kunci pencarian).
 *
 * Field yang berkaitan dengan penugasan kerja (jabatan, bagian, rincian
 * pekerjaan) dipindah konsepnya ke tabel kontraks (kolom jabatan_kontrak,
 * bagian_kontrak, rincian_pekerjaan_1/2/3 - sudah ada di sana), karena satu
 * karyawan bisa tanda tangan kontrak berkali-kali dengan jabatan/bagian yang
 * bisa berbeda tiap kontrak.
 *
 * Field lain (no_hp, email, tanggal_mulai_kerja, status_karyawan,
 * status_kepegawaian, nilai_grade) dihapus karena tidak terpakai / tidak
 * relevan dengan dokumen kontrak.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Jaga-jaga kalau tabel masih versi lama yang belum punya kolom ini
        if (!Schema::hasColumn('karyawans', 'tempat_tanggal_lahir')) {
            Schema::table('karyawans', function (Blueprint $table) {
                $table->string('tempat_tanggal_lahir')->nullable()->after('no_ktp');
            });
        }

        // jenis_kelamin diubah ke string bebas (bukan enum ketat), karena data
        // asli formatnya suka beda-beda ("Laki - Laki", "Laki-laki", dst) dan
        // enum yang terlalu ketat bikin insert gagal kalau formatnya meleset.
        if (Schema::hasColumn('karyawans', 'jenis_kelamin')) {
            Schema::table('karyawans', function (Blueprint $table) {
                $table->string('jenis_kelamin', 20)->nullable()->change();
            });
        }

        $kolomDihapus = [
            'jabatan', 'departemen', 'tempat_lahir', 'tanggal_lahir',
            'rincian_pekerjaan_1', 'rincian_pekerjaan_2', 'rincian_pekerjaan_3',
            'status_kepegawaian', 'nilai_grade',
            'no_hp', 'email', 'tanggal_mulai_kerja', 'status_karyawan',
        ];

        $kolomAda = array_filter($kolomDihapus, fn ($kolom) => Schema::hasColumn('karyawans', $kolom));

        if (!empty($kolomAda)) {
            Schema::table('karyawans', function (Blueprint $table) use ($kolomAda) {
                $table->dropColumn($kolomAda);
            });
        }
    }

    public function down(): void
    {
        Schema::table('karyawans', function (Blueprint $table) {
            $table->string('jabatan', 100)->nullable();
            $table->string('departemen', 100)->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->string('rincian_pekerjaan_1')->nullable();
            $table->string('rincian_pekerjaan_2')->nullable();
            $table->string('rincian_pekerjaan_3')->nullable();
            $table->string('status_kepegawaian')->nullable();
            $table->unsignedInteger('nilai_grade')->nullable();
            $table->string('no_hp', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->date('tanggal_mulai_kerja')->nullable();
            $table->enum('status_karyawan', ['Aktif', 'Nonaktif'])->default('Aktif');
        });
    }
};
