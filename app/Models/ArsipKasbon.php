<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArsipKasbon extends Model
{
    protected $table = 'arsip_kasbon';

    protected $fillable = [
        'tanggal_transaksi',
        'document_no',
        'numerator',
        'park_oleh',
        'nama_vendor',
        'kode_vendor',
        'cek_giro_trx',
        'deskripsi_cost_object',
        'jumlah_total',
        'terbilang',
        'file_scan',
        'file_scan_name',
        'status',
        'user_id',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'date',
        'jumlah_total'      => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(ArsipKasbonItem::class, 'arsip_kasbon_id');
    }

    public function lampiran()
    {
        return $this->hasMany(ArsipKasbonLampiran::class, 'arsip_kasbon_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}