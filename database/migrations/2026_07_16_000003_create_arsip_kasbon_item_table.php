<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Baris item per akun di dalam satu surat kasbon.
     * Satu arsip_kasbon bisa punya banyak baris (banyak No Akun berbeda),
     * persis seperti contoh fisiknya (SSD & Mouse Wireless -> 2 akun berbeda).
     */
    public function up(): void
    {
        Schema::create('arsip_kasbon_item', function (Blueprint $table) {
            $table->id();

            $table->foreignId('arsip_kasbon_id')
                ->constrained('arsip_kasbon')
                ->cascadeOnDelete();

            $table->string('no_akun', 30);
            $table->string('pk', 20)->nullable();
            $table->string('cost_object', 50)->nullable();
            $table->string('item_text')->nullable();
            $table->decimal('jumlah_rupiah', 15, 2)->default(0);

            $table->timestamps();

            $table->index('no_akun');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arsip_kasbon_item');
    }
};
