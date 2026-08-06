<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('jenis_kontrak_id')->constrained('jenis_kontraks')->cascadeOnDelete();
            $table->string('nama_template');
            $table->string('file_path'); // path relatif di storage, sama format-nya kayak jenis_kontrak->template_file
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Isi otomatis 1 template default per jenis_kontrak dari template_file
        // yang sudah ada sekarang, biar kontrak lama tetap konsisten dan
        // tombol "Ganti Template" langsung punya minimal 1 opsi existing.
        $jenisKontraks = DB::table('jenis_kontraks')->whereNotNull('template_file')->get();
        foreach ($jenisKontraks as $jenis) {
            DB::table('templates')->insert([
                'jenis_kontrak_id' => $jenis->id,
                'nama_template'    => 'Template Awal',
                'file_path'        => $jenis->template_file,
                'is_default'       => true,
                'created_at'       => now(),
                'updated_at'       => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
