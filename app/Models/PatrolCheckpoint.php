<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PatrolCheckpoint extends Model
{
    protected $table = 'patrol_checkpoints';

    protected $fillable = [
        'kode',
        'nama_titik',
        'area',
        'urutan',
        'deskripsi',
        'latitude',
        'longitude',
        'aktif',
    ];

    protected $casts = [
        'aktif'     => 'boolean',
        'urutan'    => 'integer',
        'latitude'  => 'float',
        'longitude' => 'float',
    ];

    public function scans()
    {
        return $this->hasMany(PatrolScan::class);
    }

    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }

    public function scopeUrut(Builder $query): Builder
    {
        return $query->orderBy('urutan')->orderBy('nama_titik');
    }

    /**
     * Generate kode checkpoint berikutnya, format CP-001, CP-002, dst.
     * Diambil manual di PHP (bukan raw SQL) supaya portable di SQLite & MySQL.
     */
    public static function kodeBerikutnya(): string
    {
        $terbesar = 0;

        foreach (static::query()->pluck('kode') as $kode) {
            if (preg_match('/CP-(\d+)/', $kode, $m)) {
                $terbesar = max($terbesar, (int) $m[1]);
            }
        }

        return 'CP-' . str_pad($terbesar + 1, 3, '0', STR_PAD_LEFT);
    }
}
