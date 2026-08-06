<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class WismaTamu extends Model
{
    protected $table = 'wisma_tamus';

    /**
     * Total kamar yang tersedia di Wisma Tamu.
     * Dipakai di controller & view untuk generate grid/list kamar 1 - TOTAL_KAMAR.
     */
    const TOTAL_KAMAR = 15;

    protected $fillable = [
        'nomor_kamar',
        'nama_tamu',
        'no_hp',
        'asal_instansi',
        'keperluan',
        'tanggal_checkin',
        'tanggal_checkout',
        'catatan',
    ];

    protected $casts = [
        'nomor_kamar'       => 'integer',
        'tanggal_checkin'   => 'date',
        'tanggal_checkout'  => 'date',
    ];

    /**
     * Scope: hanya tamu yang statusnya masih menginap hari ini
     * (checkin <= hari ini <= checkout). Lewat dari tanggal checkout
     * otomatis dianggap sudah keluar / kamar kosong.
     */
    public function scopeSedangMenginap(Builder $query): Builder
    {
        $today = now()->toDateString();

        return $query->whereDate('tanggal_checkin', '<=', $today)
                      ->whereDate('tanggal_checkout', '>=', $today);
    }

    public function getSedangMenginapAttribute(): bool
    {
        $today = now()->toDateString();

        return $this->tanggal_checkin->toDateString() <= $today
            && $this->tanggal_checkout->toDateString() >= $today;
    }
}