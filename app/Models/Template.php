<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Template extends Model
{
    protected $fillable = [
        'jenis_kontrak_id',
        'nama_template',
        'file_path',
        'is_default',
        'published_at',
    ];

    protected $casts = [
        'is_default'   => 'boolean',
        'published_at' => 'datetime',
    ];

    public function jenisKontrak()
    {
        return $this->belongsTo(JenisKontrak::class);
    }
}
