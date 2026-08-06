<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('karyawans', function (Blueprint $table) {
            $table->id();

            $table->string('nik', 30)->unique();
            $table->string('no_ktp', 30)->nullable();
            $table->string('nama', 150);
            $table->string('jabatan', 100)->nullable();
            $table->string('departemen', 100)->nullable();
            $table->string('tempat_lahir', 100)->nullable();
            $table->date('tanggal_lahir')->nullable();
            $table->enum('jenis_kelamin', ['Laki - Laki', 'Perempuan'])->nullable();
            $table->string('agama', 30)->nullable();
            $table->string('status_perkawinan', 30)->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_hp', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->date('tanggal_mulai_kerja')->nullable();
            $table->enum('status_karyawan', ['Aktif', 'Nonaktif'])->default('Aktif');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('karyawans');
    }
};
