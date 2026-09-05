<?php

namespace App\Http\Controllers;

use App\Models\PatrolSchedule;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class PatroliJadwalController extends Controller
{
    /**
     * Tampilan mingguan: 7 hari dari tanggal yang dipilih (default: hari ini),
     * supervisor/admin bisa lihat siapa bertugas tiap hari & tambah/hapus penugasan.
     */
    public function index(Request $request)
    {
        $mulai = $request->filled('minggu')
            ? Carbon::parse($request->minggu)->startOfWeek(Carbon::MONDAY)
            : now()->startOfWeek(Carbon::MONDAY);

        $hariRange = collect(range(0, 6))->map(fn ($i) => $mulai->copy()->addDays($i));

        $jadwal = PatrolSchedule::with('user')
            ->whereBetween('tanggal', [$mulai->toDateString(), $mulai->copy()->addDays(6)->toDateString()])
            ->orderBy('tanggal')
            ->get()
            ->groupBy(fn ($j) => $j->tanggal->toDateString());

        $petugasList = User::where('role', 'satpam')->orderBy('username')->get();

        return view('patroli.jadwal.index', [
            'hariRange'   => $hariRange,
            'jadwal'      => $jadwal,
            'petugasList' => $petugasList,
            'mingguIni'   => $mulai,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id'     => 'required|exists:users,id',
            'tanggal'     => 'required|date',
            'jam_mulai'   => 'nullable|date_format:H:i',
            'jam_selesai' => 'nullable|date_format:H:i|after:jam_mulai',
            'catatan'     => 'nullable|string|max:255',
        ]);

        $data['dibuat_oleh'] = Auth::id();

        PatrolSchedule::updateOrCreate(
            ['user_id' => $data['user_id'], 'tanggal' => $data['tanggal']],
            $data
        );

        return back()->with('success', 'Jadwal patroli berhasil disimpan.');
    }

    public function destroy(PatrolSchedule $jadwal)
    {
        $jadwal->delete();

        return back()->with('success', 'Jadwal patroli berhasil dihapus.');
    }
}
