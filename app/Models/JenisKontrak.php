<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisKontrak extends Model
{
    protected $table = 'jenis_kontraks';

    protected $fillable = [
        'kode',
        'kode_nomor',       // segmen nomor surat: KTR / PJJ
        'nama_jenis',
        'nama_singkat',     // label pendek: "PKWT" / "PKWT DMG"
        'masa_berlaku_bulan',
        'masa_giling',      // true = kontrak dibuat selama masa giling (pakai kode PJJ)
        'gaji_pokok_default',
        'template_file',
        'deskripsi',
    ];

    protected $casts = [
        'masa_giling' => 'boolean',
        'gaji_pokok_default' => 'decimal:2',
    ];

    public function kontrak()
    {
        return $this->hasMany(Kontrak::class);
    }
}
