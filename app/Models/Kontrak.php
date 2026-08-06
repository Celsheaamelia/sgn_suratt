<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kontrak extends Model
{
    protected $table = 'kontraks';

    protected $fillable = [
        'nomor_kontrak',
        'tanggal',
        'karyawan_id',
        'jenis_kontrak_id',
        'penandatangan_id',
        'template_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jabatan_kontrak',
        'bagian_kontrak',
        'rincian_pekerjaan_1',
        'rincian_pekerjaan_2',
        'rincian_pekerjaan_3',
        'gaji_pokok',
        'catatan',
        'generated_file_path',
        'signed_file_path',
        'signed_file_name',
        'signed_uploaded_at',
        'published_at',
        'status',
        'user_id',
    ];

    protected $casts = [
        'tanggal'             => 'date',
        'tanggal_mulai'       => 'date',
        'tanggal_selesai'     => 'date',
        'signed_uploaded_at'  => 'datetime',
        'published_at'        => 'datetime',
        'gaji_pokok'          => 'decimal:2',
    ];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function jenisKontrak()
    {
        return $this->belongsTo(JenisKontrak::class);
    }

    public function penandatangan()
    {
        return $this->belongsTo(Penandatangan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function template()
    {
        return $this->belongsTo(Template::class);
    }
}
