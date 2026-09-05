<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Integrasi absensi People One (SOP 1. Persiapan: "Login ke aplikasi patroli & Absensi People One").
 *
 * CATATAN PENTING: ini adalah STUB/kerangka, bukan integrasi yang sudah tersambung.
 * Saya tidak punya dokumentasi API resmi People One (base URL, format auth, endpoint,
 * bentuk response), jadi method di bawah ini perlu disesuaikan begitu dokumentasi/kredensial
 * dari tim People One tersedia. Yang sudah disiapkan:
 * - Struktur pemanggilan HTTP standar Laravel (mudah diganti endpoint/payload-nya)
 * - Titik integrasi di PatroliController::start() yang otomatis AKTIF/NONAKTIF lewat
 *   config PEOPLE_ONE_ENABLED, supaya tidak memblokir siapa pun sebelum integrasi siap.
 */
class PeopleOneService
{
    public function __construct(
        private ?string $baseUrl = null,
        private ?string $apiKey = null,
    ) {
        $this->baseUrl = $baseUrl ?? config('services.people_one.base_url');
        $this->apiKey  = $apiKey ?? config('services.people_one.api_key');
    }

    public function aktif(): bool
    {
        return (bool) config('services.people_one.enabled', false)
            && filled($this->baseUrl)
            && filled($this->apiKey);
    }

    /**
     * Cek apakah user sudah absen masuk di People One untuk tanggal tertentu.
     * TODO: sesuaikan endpoint & mapping field response begitu spesifikasi API People One ada.
     *
     * @return bool|null true = sudah absen, false = belum absen, null = tidak bisa dicek (API error/nonaktif)
     */
    public function sudahAbsenMasuk(User $user, string $tanggal): ?bool
    {
        if (! $this->aktif()) {
            return null;
        }

        // Butuh cara memetakan User lokal ke identitas People One (NIP/employee_id).
        // Placeholder: asumsikan username = NIP People One. Sesuaikan kalau beda.
        $nip = $user->username;

        try {
            $response = Http::baseUrl($this->baseUrl)
                ->withToken($this->apiKey)
                ->timeout(5)
                ->get('/api/absensi/status', [
                    'nip'     => $nip,
                    'tanggal' => $tanggal,
                ]);

            if (! $response->successful()) {
                Log::warning('People One: gagal cek status absensi', [
                    'nip' => $nip, 'status' => $response->status(),
                ]);
                return null;
            }

            // TODO: sesuaikan nama field ini dengan bentuk response asli People One.
            return (bool) ($response->json('data.sudah_absen') ?? false);
        } catch (\Throwable $e) {
            Log::warning('People One: exception saat cek absensi — ' . $e->getMessage());
            return null; // gagal terhubung tidak boleh memblokir petugas mulai patroli
        }
    }
}
