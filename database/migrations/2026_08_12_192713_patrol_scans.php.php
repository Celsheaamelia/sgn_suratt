<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patrol_scans', function (Blueprint $table) {
            $table->id();

            $table->foreignId('patrol_session_id')->constrained('patrol_sessions')->cascadeOnDelete();
            $table->foreignId('patrol_checkpoint_id')->constrained('patrol_checkpoints')->cascadeOnDelete();

            $table->enum('status', ['aman', 'temuan', 'bahaya']);
            $table->text('catatan')->nullable();
            $table->string('foto')->nullable(); // path di disk "public"
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->dateTime('scanned_at');

            // Ditangani oleh supervisor/admin setelah menindaklanjuti temuan
            $table->text('tindak_lanjut')->nullable();
            $table->dateTime('ditangani_at')->nullable();

            $table->timestamps();

            $table->unique(['patrol_session_id', 'patrol_checkpoint_id']); // satu titik cuma discan sekali per sesi
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrol_scans');
    }
};
