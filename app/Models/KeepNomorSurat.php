<?php

// namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// class KeepNomorSurat extends Model
// {
//     use HasFactory;

//     // Nama tabel di database bukan 'keep_nomor_surats' (default Laravel dari
//     // nama model), tapi 'keep_surat' — jadi harus disebut eksplisit di sini.
//     protected $table = 'keep_surat';

    // protected $fillable = [
    //     'signatory_id',
    //     'tanggal',
    //     'nomor',
    //     'status',
    // ];

//     protected $casts = [
//         'tanggal' => 'date',
//     ];

//     public function signatory()
//     {
//         return $this->belongsTo(Penandatangan::class, 'signatory_id');
//     }

//     public function contains(int $nomor): bool
//     {
//         return $nomor >= $this->nomor_awal && $nomor <= $this->nomor_akhir;
//     }
// }
