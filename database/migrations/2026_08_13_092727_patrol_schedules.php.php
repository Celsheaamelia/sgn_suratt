<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jadwal patroli: supervisor/admin menugaskan satpam untuk patroli pada tanggal tertentu.
     * Dipakai untuk memenuhi SOP 1. Persiapan -> "Supervisor memastikan jadwal patroli sudah terinput".
     */
    public function up(): void
    {
        Schema::create('patrol_schedules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // satpam yang ditugaskan
            $table->date('tanggal');
            $table->time('jam_mulai')->nullable();
            $table->time('jam_selesai')->nullable();
            $table->text('catatan')->nullable();
            $table->foreignId('dibuat_oleh')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();

            $table->unique(['user_id', 'tanggal']); // satu petugas cuma satu jadwal per tanggal
            $table->index('tanggal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrol_schedules');
    }
};
