<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patrol_checkpoints', function (Blueprint $table) {
            $table->id();

            $table->string('kode', 20)->unique();   // dipakai sebagai isi QR, mis. CP-001
            $table->string('nama_titik', 150);       // mis. "Gudang Gula - Pintu Utara"
            $table->string('area', 150)->nullable(); // mis. "Area Produksi"
            $table->unsignedInteger('urutan')->default(0);
            $table->text('deskripsi')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('aktif')->default(true);

            $table->timestamps();

            $table->index(['aktif', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrol_checkpoints');
    }
};
