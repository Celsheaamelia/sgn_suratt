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
        Schema::create('kontraks', function (Blueprint $table) {
            $table->id();

            $table->string('nomor_kontrak')->unique();
            $table->date('tanggal');

            $table->foreignId('karyawan_id')
                ->constrained('karyawans')
                ->cascadeOnDelete();

            $table->foreignId('jenis_kontrak_id')
                ->constrained('jenis_kontraks')
                ->cascadeOnDelete();

            $table->foreignId('penandatangan_id')
                ->constrained('penandatangan')
                ->cascadeOnDelete();

            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai')->nullable();

            // Snapshot jabatan/bagian pada saat kontrak dibuat. Sengaja dipisah dari
            // karyawans.jabatan supaya kontrak yang sudah jadi tidak berubah isinya
            // kalau suatu saat jabatan karyawan di database di-update/promosi.
            $table->string('jabatan_kontrak', 150)->nullable();
            $table->string('bagian_kontrak', 150)->nullable();
            $table->string('rincian_pekerjaan_1')->nullable();
            $table->string('rincian_pekerjaan_2')->nullable();
            $table->string('rincian_pekerjaan_3')->nullable();

            $table->decimal('gaji_pokok', 15, 2)->nullable();
            $table->text('catatan')->nullable();

            $table->string('generated_file_path')->nullable()
                ->comment('Path .docx hasil auto-generate dari template');
            $table->string('signed_file_path')->nullable()
                ->comment('Path file scan kontrak yang sudah ditandatangani (pdf/jpg/png)');
            $table->string('signed_file_name')->nullable();
            $table->timestamp('signed_uploaded_at')->nullable();

            $table->enum('status', ['Draft', 'Aktif', 'Selesai', 'Direservasi'])->default('Draft');

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->index(['tanggal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kontraks');
    }
};
