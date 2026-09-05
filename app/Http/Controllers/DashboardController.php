<?php

namespace App\Http\Controllers;

use App\Models\RiwayatSurat;
use App\Models\ArsipKasbon;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
     public function index()
    {
     /** @var \App\Models\User $user */
    $user = Auth::user();

    if (! $user) {
        return redirect()->route('login');
    }

    // Dashboard umum (surat/kontrak/kasbon) di luar cakupan role satpam & supervisor,
    // arahkan mereka ke halaman Patroli Digital masing-masing.
    if ($user->role === 'satpam') {
        return redirect()->route('patroli.index');
    }
    if ($user->role === 'supervisor') {
        return redirect()->route('patroli.monitoring.index');
    }

        $totalSurat  = RiwayatSurat::count();
        $suratHariIni = RiwayatSurat::whereDate('tanggal', Carbon::today())->count();
        $sudahUpload = RiwayatSurat::where('status', 'Terupload')->count();
        $belumUpload = RiwayatSurat::where('status', 'Belum Terupload')->count();

        $persenUpload = $totalSurat > 0
            ? round(($sudahUpload / $totalSurat) * 100, 1)
            : 0;

        $totalBulanIni = RiwayatSurat::whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->count();

        $bulanLalu = now()->subMonth();
        $totalBulanLalu = RiwayatSurat::whereMonth('tanggal', $bulanLalu->month)
            ->whereYear('tanggal', $bulanLalu->year)
            ->count();

        $trendPersen = $totalBulanLalu > 0
            ? round((($totalBulanIni - $totalBulanLalu) / $totalBulanLalu) * 100)
            : ($totalBulanIni > 0 ? 100 : 0);

        $riwayatTerbaru = RiwayatSurat::latest()->take(4)->get();

        $defaultEnd = Carbon::today();
        $defaultStart = Carbon::today()->subDays(6);
        $chartData = $this->hitungRentang($defaultStart, $defaultEnd);

        // ================================================================
        // STATISTIK ARSIP SPP
        // ================================================================
        $totalArsip = ArsipKasbon::count();
        $arsipTerbaru = ArsipKasbon::latest()->take(5)->get();
        $chartDataSpp = $this->hitungRentangSpp($defaultStart, $defaultEnd);

        return view('dashboard', compact(
            'totalSurat',
            'suratHariIni',
            'sudahUpload',
            'belumUpload',
            'persenUpload',
            'trendPersen',
            'riwayatTerbaru',
            'chartData',
            'defaultStart',
            'defaultEnd',
            'arsipTerbaru',
            'chartDataSpp'
        ));
    }

    public function chartRange(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end'   => 'required|date|after_or_equal:start',
        ]);

        $start = Carbon::parse($request->start)->startOfDay();
        $end   = Carbon::parse($request->end)->startOfDay();

        if ($start->diffInDays($end) > 366) {
            return response()->json([
                'message' => 'Rentang tanggal maksimal 1 tahun.',
            ], 422);
        }

        return response()->json($this->hitungRentang($start, $end));
    }

    // Endpoint baru khusus buat grafik SPP
    public function chartRangeSpp(Request $request)
    {
        $request->validate([
            'start' => 'required|date',
            'end'   => 'required|date|after_or_equal:start',
        ]);

        $start = Carbon::parse($request->start)->startOfDay();
        $end   = Carbon::parse($request->end)->startOfDay();

        if ($start->diffInDays($end) > 366) {
            return response()->json([
                'message' => 'Rentang tanggal maksimal 1 tahun.',
            ], 422);
        }

        return response()->json($this->hitungRentangSpp($start, $end));
    }

    private function hitungRentang(Carbon $start, Carbon $end): array
    {
        $labels = [];
        $data = [];

        $current = $start->copy();
        while ($current->lte($end)) {
            $labels[] = $current->translatedFormat('d M');
            $data[] = RiwayatSurat::whereDate('tanggal', $current->toDateString())->count();
            $current->addDay();
        }

        return ['labels' => $labels, 'data' => $data];
    }

    private function hitungRentangSpp(Carbon $start, Carbon $end): array
    {
        $labels = [];
        $data = [];

        $current = $start->copy();
        while ($current->lte($end)) {
            $labels[] = $current->translatedFormat('d M');
            $data[] = ArsipKasbon::whereDate('tanggal_transaksi', $current->toDateString())->count();
            $current->addDay();
        }

        return ['labels' => $labels, 'data' => $data];
    }


}
