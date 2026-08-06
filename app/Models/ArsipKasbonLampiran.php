<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArsipKasbonLampiran extends Model
{
    protected $table = 'arsip_kasbon_lampiran';

    protected $fillable = [
        'arsip_kasbon_id',
        'file_path',
        'file_name',
    ];

    public function kasbon()
    {
        return $this->belongsTo(ArsipKasbon::class, 'arsip_kasbon_id');
    }
}