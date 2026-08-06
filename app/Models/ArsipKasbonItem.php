<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArsipKasbonItem extends Model
{
    protected $table = 'arsip_kasbon_item';

    protected $fillable = [
        'arsip_kasbon_id',
        'no_akun',
        'pk',
        'cost_object',
        'item_text',
        'jumlah_rupiah',
    ];

    protected $casts = [
        'jumlah_rupiah' => 'decimal:2',
    ];

    public function kasbon()
    {
        return $this->belongsTo(ArsipKasbon::class, 'arsip_kasbon_id');
    }

    public function masterAkun()
    {
        return $this->belongsTo(MasterAkun::class, 'no_akun', 'no_akun');
    }
}
