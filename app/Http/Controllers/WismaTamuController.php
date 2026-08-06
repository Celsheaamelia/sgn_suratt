<?php

namespace App\Http\Controllers;

use App\Models\WismaTamu;
use Illuminate\Http\Request;

class WismaTamuController extends Controller
{
    public function index(Request $request)
    {
        $tamuList = WismaTamu::query()
            ->when($request->search, function ($q) use ($request) {
                $keyword = trim($request->search);
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('nama_tamu', 'like', "%{$keyword}%")
                        ->orWhere('nama_pengunjung', 'like', "%{$keyword}%")
                        ->orWhere('asal_instansi', 'like', "%{$keyword}%")
                        ->orWhere('no_hp', 'like', "%{$keyword}%")
                        ->orWhere('nomor_kamar', 'like', "%{$keyword}%");
                });
            })
            ->orderByDesc('tanggal_checkin')
            ->orderBy('nomor_kamar')
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('wisma-tamu._table', compact('tamuList'))->render();
        }

        return view('wisma-tamu.index', compact('tamuList'));
    }

    public function create()
    {
        $kamarKosong = $this->nomorKamarKosongHariIni();

        return view('wisma-tamu.create', compact('kamarKosong'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        WismaTamu::create($data);

        return redirect()->route('wisma-tamu.index')
            ->with('success', 'Data tamu berhasil disimpan. Kamar ' . $data['nomor_kamar'] . ' sekarang terisi.');
    }

    public function edit(WismaTamu $wismaTamu)
    {
        $kamarKosong = $this->nomorKamarKosongHariIni($wismaTamu->id);

        return view('wisma-tamu.edit', [
            'tamu'        => $wismaTamu,
            'kamarKosong' => $kamarKosong,
        ]);
    }

    public function update(Request $request, WismaTamu $wismaTamu)
    {
        $data = $this->validateData($request, $wismaTamu->id);

        $wismaTamu->update($data);

        return redirect()->route('wisma-tamu.index')
            ->with('success', 'Data tamu berhasil diperbarui.');
    }

    public function destroy(WismaTamu $wismaTamu)
    {
        $wismaTamu->delete();

        return redirect()->route('wisma-tamu.index')
            ->with('success', 'Data tamu berhasil dihapus. Kamar ' . $wismaTamu->nomor_kamar . ' sekarang kosong.');
    }

    /**
     * Halaman tampilan TV di resepsionis. Publik (tanpa login) karena
     * dibuka langsung di browser TV, bukan oleh admin yang login.
     */
    public function tv()
    {
        return view('wisma-tamu.tv');
    }

    /**
     * Endpoint JSON yang di-polling otomatis oleh halaman TV
     * supaya statusnya update sendiri tanpa perlu reload manual.
     */
    public function tvData()
    {
        $penghuni = WismaTamu::sedangMenginap()
            ->orderBy('nomor_kamar')
            ->get()
            ->keyBy('nomor_kamar');

        $kamar = [];
        for ($nomor = 1; $nomor <= WismaTamu::TOTAL_KAMAR; $nomor++) {
            $tamu = $penghuni->get($nomor);

            $kamar[] = [
                'nomor_kamar' => $nomor,
                'terisi'      => (bool) $tamu,
                'nama_tamu'   => $tamu->nama_tamu ?? null,
                'checkout'    => $tamu?->tanggal_checkout?->format('d M Y'),
            ];
        }

        return response()->json([
            'updated_at' => now()->format('H:i:s'),
            'kamar'      => $kamar,
        ]);
    }

    private function nomorKamarKosongHariIni(?int $ignoreId = null): array
    {
        $terisi = WismaTamu::sedangMenginap()
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->pluck('nomor_kamar')
            ->all();

        return array_values(array_diff(range(1, WismaTamu::TOTAL_KAMAR), $terisi));
    }

    private function validateData(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'nomor_kamar'       => 'required|integer|min:1|max:' . WismaTamu::TOTAL_KAMAR,
            'nama_tamu'         => 'required|string|max:150',
            'no_hp'             => 'nullable|string|max:20',
            'asal_instansi'     => 'nullable|string|max:150',
            'keperluan'         => 'nullable|string|max:255',
            'tanggal_checkin'   => 'required|date',
            'tanggal_checkout'  => 'required|date|after_or_equal:tanggal_checkin',
            'catatan'           => 'nullable|string',
        ]);

        $bentrok = WismaTamu::where('nomor_kamar', $data['nomor_kamar'])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where('tanggal_checkin', '<=', $data['tanggal_checkout'])
            ->where('tanggal_checkout', '>=', $data['tanggal_checkin'])
            ->exists();

        if ($bentrok) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'nomor_kamar' => 'Kamar ' . $data['nomor_kamar'] . ' sudah ada tamu lain di rentang tanggal tersebut.',
            ]);
        }

        return $data;
    }
}