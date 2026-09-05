<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PatrolCheckpoint;
use App\Models\PatrolSchedule;
use App\Models\PatrolScan;
use App\Models\PatrolSession;
use App\Services\PeopleOneService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PatroliController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $sesi = PatrolSession::with(['scans.checkpoint'])
            ->where('user_id', $user->id)
            ->hariIni()
            ->latest('mulai_at')
            ->first();

        $checkpoints = PatrolCheckpoint::aktif()->urut()->get();

        $scanByCheckpoint = $sesi
            ? $sesi->scans->keyBy('patrol_checkpoint_id')
            : collect();

        // SOP 1. Persiapan: supervisor harus sudah menginput jadwal patroli.
        // Ditampilkan sebagai info, tidak memblokir "Mulai Patroli" supaya sistem tidak macet
        // kalau admin lupa input jadwal, tapi petugas & supervisor tetap sadar statusnya.
        $jadwalHariIni = PatrolSchedule::where('user_id', $user->id)->hariIni()->first();

        // Titik checkpoint berikutnya yang wajib discan sesuai urutan rute, dipakai
        // untuk menandai titik-titik lain sebagai "terkunci" di tampilan satpam.
        $urutanBerikutnya = null;
        if ($sesi) {
            $titikBelumScan = $checkpoints->whereNotIn('id', $scanByCheckpoint->keys())->sortBy('urutan')->first();
            $urutanBerikutnya = $titikBelumScan?->urutan;
        }

        return view('patroli.satpam.index', [
            'sesi'             => $sesi,
            'checkpoints'      => $checkpoints,
            'scanByCheckpoint' => $scanByCheckpoint,
            'jadwalHariIni'    => $jadwalHariIni,
            'urutanBerikutnya' => $urutanBerikutnya,
        ]);
    }

    /**
     * Mulai shift patroli baru untuk hari ini.
     */
    public function start(PeopleOneService $peopleOne)
    {
        $user = Auth::user();

        $sesiBerjalan = PatrolSession::where('user_id', $user->id)
            ->berjalan()
            ->hariIni()
            ->first();

        if ($sesiBerjalan) {
            return redirect()->route('patroli.index')
                ->with('info', 'Anda sudah memiliki shift patroli yang sedang berjalan.');
        }

        // SOP 1. Persiapan: "Login ke aplikasi patroli & Absensi People One". Cek ini HANYA
        // dijalankan kalau integrasi People One sudah diaktifkan lewat config (lihat
        // PeopleOneService); selama belum aktif atau API gagal dihubungi, TIDAK memblokir
        // petugas mulai patroli — supaya sistem tidak macet karena dependensi eksternal.
        if ($peopleOne->aktif()) {
            $sudahAbsen = $peopleOne->sudahAbsenMasuk($user, now()->toDateString());

            if ($sudahAbsen === false) {
                return redirect()->route('patroli.index')
                    ->with('error', 'Anda belum tercatat absen masuk di People One hari ini. Silakan absen dulu sebelum memulai patroli.');
            }
            // $sudahAbsen === null (API gagal/tidak bisa dicek) sengaja tidak diblokir.
        }

        PatrolSession::create([
            'user_id'          => $user->id,
            'tanggal'          => now()->toDateString(),
            'mulai_at'         => now(),
            'status'           => 'berjalan',
            'total_checkpoint' => PatrolCheckpoint::aktif()->count(),
        ]);

        return redirect()->route('patroli.index')
            ->with('success', 'Shift patroli dimulai. Silakan scan titik pertama.');
    }

    /**
     * Form input hasil scan untuk satu titik (dibuka setelah QR di-scan atau dipilih manual).
     */
    public function scanForm(string $kode)
    {
        $checkpoint = PatrolCheckpoint::aktif()->where('kode', $kode)->firstOrFail();

        $sesi = $this->sesiAktifOrFail();

        $sudahScan = PatrolScan::where('patrol_session_id', $sesi->id)
            ->where('patrol_checkpoint_id', $checkpoint->id)
            ->first();

        if ($sudahScan) {
            return redirect()->route('patroli.index')
                ->with('info', "Titik {$checkpoint->nama_titik} sudah dicatat pada shift ini.");
        }

        // SOP 2. Pelaksanaan Patroli: "menjalankan patroli sesuai rute" — titik harus
        // discan berurutan mengikuti kolom `urutan`, bukan bebas pilih titik manapun.
        if ($titikBerikutnya = $this->titikBerikutnyaYangHarusDiscan($sesi, $checkpoint)) {
            return redirect()->route('patroli.index')->with(
                'error',
                "Belum sesuai urutan rute. Titik berikutnya yang harus discan adalah \"{$titikBerikutnya->nama_titik}\" ({$titikBerikutnya->kode})."
            );
        }

        return view('patroli.satpam.scan', [
            'checkpoint' => $checkpoint,
            'sesi'       => $sesi,
        ]);
    }

    /**
     * Simpan hasil scan/laporan untuk satu titik.
     */
    public function store(Request $request, string $kode)
{
    $checkpoint = PatrolCheckpoint::aktif()->where('kode', $kode)->firstOrFail();
    $sesi = $this->sesiAktifOrFail();

    $sudahScan = PatrolScan::where('patrol_session_id', $sesi->id)
        ->where('patrol_checkpoint_id', $checkpoint->id)
        ->exists();

    if ($sudahScan) {
        return redirect()->route('patroli.index')
            ->with('info', "Titik {$checkpoint->nama_titik} sudah dicatat pada shift ini.");
    }

    // Validasi cadangan sisi server: tolak submit kalau ada titik sebelumnya (sesuai urutan)
    // yang belum discan, meski request langsung ke endpoint store (bypass form scanForm).
    if ($titikBerikutnya = $this->titikBerikutnyaYangHarusDiscan($sesi, $checkpoint)) {
        return redirect()->route('patroli.index')->with(
            'error',
            "Belum sesuai urutan rute. Titik berikutnya yang harus discan adalah \"{$titikBerikutnya->nama_titik}\" ({$titikBerikutnya->kode})."
        );
    }

    $data = $request->validate([
        'status'    => ['required', Rule::in(['aman', 'temuan', 'bahaya'])],
        'catatan'   => ['nullable', 'string', 'max:1000'],
        // Foto/video wajib kalau status bukan "aman", sesuai SOP: "Upload bukti" untuk temuan
        'foto'      => [Rule::requiredIf(fn () => $request->status !== 'aman'), 'nullable', 'mimes:jpg,jpeg,png,webp,mp4,mov,webm', 'max:20480'],
        // GPS WAJIB aktif sesuai SOP 1. Persiapan: "Memastikan GPS dan koneksi aktif".
        // Form di sisi klien sudah memblokir submit tanpa lokasi, ini validasi cadangan di server.
        'latitude'  => ['required', 'numeric', 'between:-90,90'],
        'longitude' => ['required', 'numeric', 'between:-180,180'],
    ], [
        'foto.required_if' => 'Foto/video bukti wajib diupload untuk laporan temuan/potensi bahaya.',
        'latitude.required'  => 'Lokasi GPS wajib aktif sebelum laporan bisa dikirim. Aktifkan GPS lalu coba lagi.',
        'longitude.required' => 'Lokasi GPS wajib aktif sebelum laporan bisa dikirim. Aktifkan GPS lalu coba lagi.',
    ]);

    if ($request->hasFile('foto')) {
        $data['foto'] = $request->file('foto')->store('patroli-scan', 'public');
    }

    // Hitung jarak lokasi petugas ke koordinat checkpoint terdaftar (kalau ada), untuk audit.
    $jarak = null;
    if ($checkpoint->latitude && $checkpoint->longitude) {
        $jarak = (int) round($this->hitungJarakMeter(
            (float) $checkpoint->latitude,
            (float) $checkpoint->longitude,
            (float) $data['latitude'],
            (float) $data['longitude']
        ));
    }

    $scan = PatrolScan::create([
        'patrol_session_id'    => $sesi->id,
        'patrol_checkpoint_id' => $checkpoint->id,
        'status'               => $data['status'],
        'catatan'              => $data['catatan'] ?? null,
        'foto'                 => $data['foto'] ?? null,
        'latitude'             => $data['latitude'],
        'longitude'            => $data['longitude'],
        'jarak_meter'          => $jarak,
        'scanned_at'           => now(),
    ]);

    if (in_array($data['status'], ['temuan', 'bahaya'], true)) {
        $scan->load('checkpoint', 'session.user');

        $supervisors = \App\Models\User::whereIn('role', [
            \App\Models\User::ROLE_ADMIN,
            \App\Models\User::ROLE_SUPERVISOR,
        ])->get();

        \Illuminate\Support\Facades\Notification::send(
            $supervisors,
            new \App\Notifications\TemuanPatroliDitemukan($scan)
        );
    }

    $pesan = $data['status'] === 'aman'
        ? "Titik {$checkpoint->nama_titik} tercatat aman."
        : "Laporan untuk {$checkpoint->nama_titik} terkirim, supervisor akan diberi notifikasi.";

    return redirect()->route('patroli.index')->with('success', $pesan);
}

    /**
     * Tutup shift patroli hari ini.
     */
    public function finish()
    {
        $sesi = $this->sesiAktifOrFail();

        $sesi->update([
            'selesai_at' => now(),
            'status'     => 'selesai',
        ]);

        return redirect()->route('patroli.index')
            ->with('success', 'Patroli selesai. Terima kasih, hasil sudah dikirim ke sistem.');
    }

    /**
     * Riwayat shift milik petugas yang sedang login.
     */
    public function riwayat(Request $request)
    {
        $sesiList = PatrolSession::with('user')
            ->where('user_id', Auth::id())
            ->when($request->tanggal, fn ($q) => $q->whereDate('tanggal', $request->tanggal))
            ->orderByDesc('tanggal')
            ->orderByDesc('mulai_at')
            ->paginate(10)
            ->withQueryString();

        return view('patroli.satpam.riwayat', compact('sesiList'));
    }

    /**
     * Detail satu shift milik petugas sendiri (read-only).
     */
    public function riwayatShow(PatrolSession $sesi)
    {
        abort_unless($sesi->user_id === Auth::id(), 403);

        $sesi->load(['scans.checkpoint', 'user']);

        return view('patroli.session-detail', [
            'sesi'    => $sesi,
            'isAdmin' => false,
        ]);
    }

    /**
     * Jarak antar 2 koordinat pakai formula Haversine, hasil dalam meter.
     */
    private function hitungJarakMeter(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $bumiRadiusMeter = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $bumiRadiusMeter * $c;
    }

    /**
     * SOP mensyaratkan patroli "sesuai rute". Checkpoint diurutkan lewat kolom `urutan`
     * (lihat PatrolCheckpoint::scopeUrut). Kalau ada checkpoint aktif dengan urutan lebih
     * kecil dari checkpoint yang mau discan dan BELUM tercatat di sesi ini, maka checkpoint
     * yang mau discan sekarang belum boleh — kembalikan titik yang seharusnya discan duluan.
     *
     * Checkpoint dengan urutan sama dianggap boleh dalam urutan bebas relatif satu sama lain
     * (mis. dua titik paralel), jadi hanya urutan LEBIH KECIL yang memblokir.
     * Return null kalau checkpoint ini memang boleh discan sekarang.
     */
    private function titikBerikutnyaYangHarusDiscan(PatrolSession $sesi, PatrolCheckpoint $checkpointDituju): ?PatrolCheckpoint
    {
        $sudahDiscanIds = PatrolScan::where('patrol_session_id', $sesi->id)
            ->pluck('patrol_checkpoint_id');

        $titikTerlewat = PatrolCheckpoint::aktif()
            ->where('urutan', '<', $checkpointDituju->urutan)
            ->whereNotIn('id', $sudahDiscanIds)
            ->urut()
            ->first();

        return $titikTerlewat;
    }

    private function sesiAktifOrFail(): PatrolSession
    {
        $sesi = PatrolSession::where('user_id', Auth::id())->berjalan()->hariIni()->first();

        abort_if(! $sesi, 400, 'Anda belum memulai shift patroli hari ini.');

        return $sesi;
    }
}
