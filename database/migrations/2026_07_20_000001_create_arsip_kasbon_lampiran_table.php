<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lampiran tambahan (bisa lebih dari satu) untuk 1 arsip SPP —
     * misal kertas pendukung lain yang perlu ikut diarsipkan bareng surat utamanya.
     */
    public function up(): void
    {
        Schema::create('arsip_kasbon_lampiran', function (Blueprint $table) {
            $table->id();

            $table->foreignId('arsip_kasbon_id')
                ->constrained('arsip_kasbon')
                ->cascadeOnDelete();

            $table->string('file_path');
            $table->string('file_name')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('arsip_kasbon_lampiran');
    }
};