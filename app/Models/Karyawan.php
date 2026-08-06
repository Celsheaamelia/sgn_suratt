<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Karyawan extends Model
{
    protected $table = 'karyawans';

    protected $fillable = [
        'nik',
        'no_ktp',
        'nama',
        'tempat_tanggal_lahir',
        'jenis_kelamin',
        'agama',
        'status_perkawinan',
        'alamat',
    ];

    public function kontrak()
    {
        return $this->hasMany(Kontrak::class);
    }

    /**
     * Kontrak paling baru milik karyawan ini (berdasarkan tanggal kontrak),
     * dipakai buat nampilin status kepegawaian (PKWT DMG / PKWT DMG-LMG)
     * di tabel Data Karyawan tanpa perlu isi kolom terpisah secara manual.
     */
    public function latestKontrak()
    {
        return $this->hasOne(Kontrak::class)->latestOfMany('tanggal');
    }
}