<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Header arsip Surat Permintaan Pembayaran / kasbon.
     * Field-field ini yang diusahakan terisi otomatis dari hasil scan (OCR),
     * lalu diverifikasi/dikoreksi admin sebelum disimpan permanen.
     */
    public function up(): void
    {
        Schema::create('arsip_kasbon', function (Blueprint $table) {
            $table->id();

            $table->date('tanggal_transaksi')->nullable();
            $table->string('document_no', 50)->nullable();
            $table->string('park_oleh', 100)->nullable();
            $table->string('nama_vendor', 150)->nullable();
            $table->string('kode_vendor', 50)->nullable();
            $table->string('cek_giro_trx', 100)->nullable();
            $table->string('deskripsi_cost_object', 150)->nullable();

            $table->decimal('jumlah_total', 15, 2)->nullable();
            $table->string('terbilang')->nullable();

            // Path file scan asli, tetap disimpan sebagai arsip meski data sudah diketik ulang
            $table->string('file_scan')->nullable();
            $table->string('file_scan_name')->nullable();

            // 'draft'  -> baru discan, belum diverifikasi admin
            // 'arsip'  -> sudah diverifikasi & disimpan final
            $table->string('status', 20)->default('draft');

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arsip_kasbon');
    }
};
