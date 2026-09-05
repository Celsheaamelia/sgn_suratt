<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PatrolSession extends Model
{
    protected $table = 'patrol_sessions';

    protected $fillable = [
        'user_id',
        'tanggal',
        'mulai_at',
        'selesai_at',
        'status',
        'total_checkpoint',
    ];

    protected $casts = [
        'tanggal'    => 'date',
        'mulai_at'   => 'datetime',
        'selesai_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scans()
    {
        return $this->hasMany(PatrolScan::class);
    }

    /**
     * Jadwal (jam_mulai/jam_selesai) yang berkaitan dengan sesi ini, dicocokkan lewat
     * user_id + tanggal. Bukan relasi FK asli karena jadwal & sesi memang tabel independen
     * di desain saat ini — makanya diambil manual, bukan lewat method relasi Eloquent.
     */
    public function getJadwalAttribute(): ?PatrolSchedule
    {
        return PatrolSchedule::where('user_id', $this->user_id)
            ->whereDate('tanggal', $this->tanggal)
            ->first();
    }

    public function scopeBerjalan(Builder $query): Builder
    {
        return $query->where('status', 'berjalan');
    }

    public function scopeHariIni(Builder $query): Builder
    {
        return $query->whereDate('tanggal', now()->toDateString());
    }

    public function getJumlahScanAttribute(): int
    {
        return $this->scans()->count();
    }

    public function getJumlahTemuanAttribute(): int
    {
        return $this->scans()->whereIn('status', ['temuan', 'bahaya'])->count();
    }

    public function getProgresPersenAttribute(): int
    {
        if (! $this->total_checkpoint) {
            return 0;
        }

        return (int) round(($this->jumlah_scan / $this->total_checkpoint) * 100);
    }

    /**
     * Sesi dianggap terlambat kalau:
     * - Ada jadwal untuk hari itu dengan jam_selesai, dan sekarang sudah lewat jam_selesai
     *   tersebut padahal sesi belum ditutup (lebih akurat sesuai jadwal per-shift); atau
     * - Tidak ada jadwal (fallback), pakai aturan lama: lewat 4 jam sejak mulai & belum selesai.
     */
    public function getTerlambatAttribute(): bool
    {
        if ($this->status !== 'berjalan' || ! $this->mulai_at) {
            return false;
        }

        $jadwal = $this->jadwal;

        if ($jadwal && $jadwal->jam_selesai) {
            $batasWaktu = \Illuminate\Support\Carbon::parse($this->tanggal->toDateString() . ' ' . $jadwal->jam_selesai);
            return now()->greaterThan($batasWaktu);
        }

        return $this->mulai_at->diffInHours(now()) >= 4;
    }
}
