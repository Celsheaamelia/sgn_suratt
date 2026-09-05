<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patrol_sessions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('tanggal');
            $table->dateTime('mulai_at');
            $table->dateTime('selesai_at')->nullable();
            $table->enum('status', ['berjalan', 'selesai'])->default('berjalan');
            $table->unsignedInteger('total_checkpoint')->default(0); // snapshot jumlah titik aktif saat mulai shift

            $table->timestamps();

            $table->index(['user_id', 'tanggal']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patrol_sessions');
    }
};
