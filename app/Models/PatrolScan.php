<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PatrolScan extends Model
{
    protected $table = 'patrol_scans';

    protected $fillable = [
        'patrol_session_id',
        'patrol_checkpoint_id',
        'status',
        'catatan',
        'foto',
        'latitude',
        'longitude',
        'scanned_at',
        'tindak_lanjut',
        'ditangani_at',
        'eskalasi_level',
        'eskalasi_terakhir_at',
    ];

    protected $casts = [
        'scanned_at'            => 'datetime',
        'ditangani_at'          => 'datetime',
        'eskalasi_terakhir_at'  => 'datetime',
        'latitude'              => 'float',
        'longitude'             => 'float',
    ];

    public function session()
    {
        return $this->belongsTo(PatrolSession::class, 'patrol_session_id');
    }

    public function checkpoint()
    {
        return $this->belongsTo(PatrolCheckpoint::class, 'patrol_checkpoint_id');
    }

    public function scopeTemuan(Builder $query): Builder
    {
        return $query->whereIn('status', ['temuan', 'bahaya']);
    }

    public function scopeBelumDitangani(Builder $query): Builder
    {
        return $query->whereNull('ditangani_at');
    }

    public function getLabelStatusAttribute(): string
    {
        return match ($this->status) {
            'aman'   => 'Aman',
            'temuan' => 'Temuan',
            'bahaya' => 'Potensi Bahaya',
            default  => ucfirst($this->status),
        };
    }
}
