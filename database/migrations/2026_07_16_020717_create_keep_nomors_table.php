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
        Schema::create('keep_surat', function (Blueprint $table) {
            $table->id();

            $table->foreignId('signatory_id')
                ->constrained('penandatangan')
                ->onDelete('cascade');

            $table->date('tanggal');

            // Satu row = satu nomor
            $table->unsignedInteger('nomor');

            $table->enum('status', ['aktif', 'terpakai'])->default('aktif');

            $table->timestamps();

            $table->index(['tanggal', 'signatory_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('keep_surat');
    }
};
