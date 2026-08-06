<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterAkun extends Model
{
    protected $table = 'master_akun';

    protected $fillable = [
        'no_akun',
        'deskripsi',
    ];

    public function items()
    {
        return $this->hasMany(ArsipKasbonItem::class, 'no_akun', 'no_akun');
    }
}
