<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;
use App\Models\Kontrak;

class KaryawanController extends Controller
{
    /**
     * Pisah query jadi kata per kata, tiap kata harus ketemu di salah satu
     * kolom (nama/nik/no_ktp) - supaya "Abd Qodir" tetap ketemu "Abdul Qodir",
     * bukan cuma exact substring match kayak sebelumnya.
     */
    private function applySearch($query, string $search)
    {
        $words = array_filter(preg_split('/\s+/', trim($search)));

        foreach ($words as $word) {
            $query->where(function ($q) use ($word) {
                $q->where('nama', 'like', "%{$word}%")
                  ->orWhere('nik', 'like', "%{$word}%")
                  ->orWhere('no_ktp', 'like', "%{$word}%");
            });
        }

        return $query;
    }

    public function index(Request $request)
    {
        $karyawanList = Karyawan::query()
            ->with(['latestKontrak.jenisKontrak'])
            ->when($request->search, fn ($q) => $this->applySearch($q, $request->search))
            ->when($request->status_kontrak, function ($q) use ($request) {
                $masaGiling = $request->status_kontrak === 'dmg';
                $q->whereHas('latestKontrak.jenisKontrak', function ($qq) use ($masaGiling) {
                    $qq->where('masa_giling', $masaGiling);
                });
            })
            // Filter Bagian: dicek dari bagian_kontrak di kontrak PALING BARU
            // milik karyawan, konsisten sama filter status_kontrak di atas.
            ->when($request->bagian, function ($q) use ($request) {
                $q->whereHas('latestKontrak', function ($qq) use ($request) {
                    $qq->where('bagian_kontrak', $request->bagian);
                });
            })
            ->orderBy('nama')
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('karyawan._table', compact('karyawanList'))->render();
        }

        // Daftar Bagian unik yang beneran ada di data, buat isi dropdown filter -
        // diambil dari kontrak TERBARU tiap karyawan (bukan semua histori kontrak),
        // biar konsisten sama logika filter status_kontrak.
        $bagianList = Kontrak::query()
            ->whereIn('id', function ($sub) {
                $sub->selectRaw('MAX(id)')
                    ->from('kontraks')
                    ->groupBy('karyawan_id');
            })
            ->whereNotNull('bagian_kontrak')
            ->where('bagian_kontrak', '!=', '')
            ->distinct()
            ->orderBy('bagian_kontrak')
            ->pluck('bagian_kontrak');

        return view('karyawan.index', compact('karyawanList', 'bagianList'));
}

    public function create()
    {
        return view('karyawan.create');
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        Karyawan::create($data);

        return redirect()->route('karyawan.index')
            ->with('success', 'Data karyawan berhasil ditambahkan.');
    }

    public function edit(Karyawan $karyawan)
    {
        return view('karyawan.edit', compact('karyawan'));
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        $data = $this->validateData($request, $karyawan->id);

        $karyawan->update($data);

        return redirect()->route('karyawan.index')
            ->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Karyawan $karyawan)
    {
        $karyawan->delete();

        return redirect()->route('karyawan.index')
            ->with('success', 'Data karyawan berhasil dihapus.');
    }

    private function validateData(Request $request, $ignoreId = null): array
    {
        return $request->validate([
            'nik'                   => 'required|string|max:30|unique:karyawans,nik' . ($ignoreId ? ",{$ignoreId}" : ''),
            'no_ktp'                => 'nullable|string|max:30',
            'nama'                  => 'required|string|max:150',
            'tempat_tanggal_lahir'  => 'nullable|string|max:255',
            'jenis_kelamin'         => 'nullable|in:Laki-laki,Perempuan',
            'agama'                 => 'nullable|string|max:30',
            'status_perkawinan'     => 'nullable|string|max:30',
            'alamat'                => 'nullable|string',
        ]);
    }

    public function search(Request $request)
    {
        $q = $request->get('q', '');

        $result = Karyawan::query()
            ->with('latestKontrak')
            ->when($q, fn ($query) => $this->applySearch($query, $q))
            ->orderBy('nama')
            ->limit(15)
            ->get(['id', 'nik', 'no_ktp', 'nama', 'tempat_tanggal_lahir', 'jenis_kelamin', 'agama', 'status_perkawinan', 'alamat'])
            ->map(function ($k) {
                return [
                    'id'                    => $k->id,
                    'nik'                   => $k->nik,
                    'no_ktp'                => $k->no_ktp,
                    'nama'                  => $k->nama,
                    'tempat_tanggal_lahir'  => $k->tempat_tanggal_lahir,
                    'jenis_kelamin'         => $k->jenis_kelamin,
                    'agama'                 => $k->agama,
                    'status_perkawinan'     => $k->status_perkawinan,
                    'alamat'                => $k->alamat,
                    'jenis_kontrak_id_terakhir' => $k->latestKontrak->jenis_kontrak_id ?? null,
                    'jabatan_terakhir'      => $k->latestKontrak->jabatan_kontrak ?? null,
                    'bagian_terakhir'       => $k->latestKontrak->bagian_kontrak ?? null,
                    'rincian_1_terakhir'    => $k->latestKontrak->rincian_pekerjaan_1 ?? null,
                    'rincian_2_terakhir'    => $k->latestKontrak->rincian_pekerjaan_2 ?? null,
                    'rincian_3_terakhir'    => $k->latestKontrak->rincian_pekerjaan_3 ?? null,
                ];
            });

        return response()->json($result);
    }
}
