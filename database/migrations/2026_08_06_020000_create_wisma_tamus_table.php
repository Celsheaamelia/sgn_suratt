<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wisma_tamus', function (Blueprint $table) {
            $table->id();

            $table->unsignedTinyInteger('nomor_kamar'); // 1 - 15
            $table->string('nama_tamu', 150);
            $table->string('nama_pengunjung', 150)->nullable(); // rombongan / pendamping tamu utama
            $table->string('no_hp', 20)->nullable();
            $table->string('asal_instansi', 150)->nullable();
            $table->string('keperluan', 255)->nullable();
            $table->date('tanggal_checkin');
            $table->date('tanggal_checkout'); // rencana checkout, dipakai untuk nentuin kamar masih terisi atau udah kosong
            $table->text('catatan')->nullable();

            $table->timestamps();

            $table->index('nomor_kamar');
            $table->index(['tanggal_checkin', 'tanggal_checkout']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wisma_tamus');
    }
};
