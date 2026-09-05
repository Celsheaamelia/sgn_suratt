<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PatrolSchedule extends Model
{
    protected $table = 'patrol_schedules';

    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_mulai',
        'jam_selesai',
        'catatan',
        'dibuat_oleh',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function scopeHariIni(Builder $query): Builder
    {
        return $query->whereDate('tanggal', now()->toDateString());
    }

    public function scopeUntukTanggal(Builder $query, string $tanggal): Builder
    {
        return $query->whereDate('tanggal', $tanggal);
    }
}
