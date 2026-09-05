<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PatrolCheckpoint;
use App\Models\PatrolScan;
use App\Models\PatrolSession;
use Illuminate\Http\Request;

class MonitoringController extends Controller
{
    /**
     * Dashboard utama: ringkasan hari ini + daftar petugas aktif + temuan terbaru.
     */
    public function index()
    {
        return view('patroli.monitoring.index', $this->ringkasanHariIni());
    }

    /**
     * Endpoint JSON yang di-polling otomatis dari dashboard supaya "real-time"
     * (pola yang sama seperti halaman TV Wisma Tamu).
     */
    public function data()
    {
        return response()->json($this->ringkasanHariIni(forJson: true));
    }

    /**
     * Riwayat semua shift patroli (lintas petugas), bisa difilter tanggal & petugas.
     */
    public function riwayat(Request $request)
    {
        $sesiList = PatrolSession::with('user')
            ->when($request->tanggal, fn ($q) => $q->whereDate('tanggal', $request->tanggal))
            ->when($request->user_id, fn ($q) => $q->where('user_id', $request->user_id))
            ->orderByDesc('tanggal')
            ->orderByDesc('mulai_at')
            ->paginate(15)
            ->withQueryString();

        $petugasList = \App\Models\User::where('role', 'satpam')->orderBy('username')->get();

        return view('patroli.monitoring.riwayat', compact('sesiList', 'petugasList'));
    }

    /**
     * Detail satu sesi patroli: timeline scan tiap titik + aksi tindak lanjut temuan.
     */
    public function show(PatrolSession $sesi)
    {
        $sesi->load(['scans.checkpoint', 'user']);

        return view('patroli.session-detail', [
            'sesi'    => $sesi,
            'isAdmin' => true,
        ]);
    }

    /**
     * Supervisor menandai temuan sudah ditindaklanjuti.
     */
    public function tindakLanjut(Request $request, PatrolScan $scan)
    {
        $request->validate([
            'tindak_lanjut' => 'required|string|max:1000',
        ]);

        $scan->update([
            'tindak_lanjut' => $request->tindak_lanjut,
            'ditangani_at'  => now(),
        ]);

        return back()->with('success', 'Tindak lanjut berhasil dicatat.');
    }

    private function ringkasanHariIni(bool $forJson = false): array
    {
        $sesiHariIni = PatrolSession::with(['user', 'scans'])
            ->hariIni()
            ->orderByDesc('mulai_at')
            ->get();

        $totalCheckpointAktif = PatrolCheckpoint::aktif()->count();

        $temuanTerbaru = PatrolScan::with(['checkpoint', 'session.user'])
            ->temuan()
            ->whereHas('session', fn ($q) => $q->hariIni())
            ->latest('scanned_at')
            ->limit(10)
            ->get();

        // ===== Data peta: titik checkpoint + posisi terakhir tiap petugas yang sesinya berjalan =====
        // SOP bagian B.1: "Peta area pabrik" + "Posisi petugas (ikon bergerak)".
        // Posisi petugas diambil dari koordinat GPS scan checkpoint TERAKHIR pada sesi yang
        // masih berjalan (sistem ini tidak melacak GPS kontinu di antar-titik, hanya per-scan,
        // jadi ikon "bergerak" berpindah tiap kali petugas menyelesaikan satu titik checkpoint).
        $checkpointPeta = PatrolCheckpoint::aktif()
            ->whereNotNull('latitude')->whereNotNull('longitude')
            ->urut()
            ->get(['id', 'kode', 'nama_titik', 'area', 'latitude', 'longitude']);

        $posisiPetugas = $sesiHariIni
            ->where('status', 'berjalan')
            ->map(function (PatrolSession $s) {
                $scanTerakhir = $s->scans->sortByDesc('scanned_at')->first();

                if (! $scanTerakhir || ! $scanTerakhir->latitude || ! $scanTerakhir->longitude) {
                    return null; // belum ada scan ber-GPS, tidak bisa ditampilkan di peta
                }

                return [
                    'session_id' => $s->id,
                    'petugas'    => $s->user->username ?? '-',
                    'latitude'   => $scanTerakhir->latitude,
                    'longitude'  => $scanTerakhir->longitude,
                    'titik'      => $scanTerakhir->checkpoint->nama_titik ?? '-',
                    'waktu'      => optional($scanTerakhir->scanned_at)->format('H:i'),
                    'terlambat'  => $s->terlambat,
                    'detail_url' => route('patroli.monitoring.show', $s->id),
                ];
            })
            ->filter()
            ->values();

        $data = [
            'sesiHariIni'          => $sesiHariIni,
            'petugasAktif'         => $sesiHariIni->where('status', 'berjalan')->count(),
            'totalCheckpointAktif' => $totalCheckpointAktif,
            'temuanTerbaru'        => $temuanTerbaru,
            'checkpointPeta'       => $checkpointPeta,
            'posisiPetugas'        => $posisiPetugas,
            'totalTemuanHariIni'   => $temuanTerbaru->count() + PatrolScan::temuan()
                ->whereHas('session', fn ($q) => $q->hariIni())
                ->count() - $temuanTerbaru->count(), // dihitung ulang di bawah biar akurat
            'updatedAt'            => now()->format('H:i:s'),
        ];

        // Hitung ulang total temuan hari ini secara akurat (tanpa limit 10)
        $data['totalTemuanHariIni'] = PatrolScan::temuan()
            ->whereHas('session', fn ($q) => $q->hariIni())
            ->count();

        if ($forJson) {
            // Bentuk ringan untuk JSON polling, hindari kirim relasi berat / model penuh
            $data['sesiHariIni'] = $sesiHariIni->map(function (PatrolSession $s) {
                return [
                    'id'               => $s->id,
                    'petugas'          => $s->user->username ?? '-',
                    'status'           => $s->status,
                    'terlambat'        => $s->terlambat,
                    'mulai_at'         => optional($s->mulai_at)->format('H:i'),
                    'selesai_at'       => optional($s->selesai_at)->format('H:i'),
                    'jumlah_scan'      => $s->jumlah_scan,
                    'total_checkpoint' => $s->total_checkpoint,
                    'progres_persen'   => $s->progres_persen,
                    'jumlah_temuan'    => $s->jumlah_temuan,
                    'detail_url'       => route('patroli.monitoring.show', $s->id),
                ];
            })->values();

            $data['temuanTerbaru'] = $temuanTerbaru->map(function (PatrolScan $t) {
                return [
                    'id'         => $t->id,
                    'titik'      => $t->checkpoint->nama_titik ?? '-',
                    'petugas'    => $t->session->user->username ?? '-',
                    'status'     => $t->status,
                    'label'      => $t->label_status,
                    'catatan'    => $t->catatan,
                    'waktu'      => $t->scanned_at?->format('H:i'),
                    'ditangani'  => (bool) $t->ditangani_at,
                    'detail_url' => route('patroli.monitoring.show', $t->patrol_session_id),
                ];
            })->values();

            // posisiPetugas sudah berbentuk array asosiatif ringan, aman dikirim langsung.
            $data['checkpointPeta'] = $checkpointPeta->values();
        }

        return $data;
    }
}
