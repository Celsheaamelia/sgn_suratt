<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel referensi "kamus" No Akun -> Deskripsi.
     * Dipakai untuk auto-fill deskripsi saat input No Akun,
     * dan untuk fitur pencarian "No Akun sekian, deskripsinya apa".
     */
    public function up(): void
    {
        Schema::create('master_akun', function (Blueprint $table) {
            $table->id();
            $table->string('no_akun', 30)->unique();
            $table->string('deskripsi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_akun');
    }
};
