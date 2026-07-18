<?php

namespace App\Http\Controllers;

use App\Models\KeepNomorSurat;
use App\Models\Penandatangan;
use App\Models\TujuanSurat;
use App\Models\KlasifikasiSurat;
use App\Models\RiwayatSurat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class KeepController extends Controller
{
    use NomorUrut;

    public function keepNomorSurat()
    {
        $penandatanganList = Penandatangan::orderByRaw("
        CASE
            WHEN jabatan = 'General Manager' THEN 1
            WHEN jabatan = 'Manager' THEN 2
            WHEN jabatan = 'Asisten Manager' THEN 3
            ELSE 4
        END
    ")->get();
        $tujuanList        = TujuanSurat::orderBy('kode')->get();
        $klasifikasiList   = KlasifikasiSurat::orderBy('kode')->get();

        $keepList = KeepNomorSurat::with('signatory')
            ->orderByDesc('tanggal')
            ->orderBy('nomor')
            ->get();

        return view('keepnomorsurat', compact(
            'penandatanganList',
            'tujuanList',
            'klasifikasiList',
            'keepList'
        ));
    }

    public function storeKeepNomor(Request $request)
    {
        $validated = $request->validate([
            'signatory'   => 'required|exists:penandatangan,id',
            'tanggal'     => 'required|date',
            'nomor_awal'  => 'required|integer|min:1',
            'nomor_akhir' => 'required|integer|min:1|gte:nomor_awal',
        ]);

        $used      = $this->usedNumbersForDate($validated['tanggal']);
        $requested = range($validated['nomor_awal'], $validated['nomor_akhir']);
        $conflict  = array_values(array_intersect($requested, $used));

        if (!empty($conflict)) {
            sort($conflict);
            $list = implode(', #', array_map(fn ($n) => str_pad($n, 3, '0', STR_PAD_LEFT), $conflict));

            return back()
                ->withInput()
                ->with('error', "Nomor #{$list} di tanggal ini sudah dipakai atau sudah di-keep. Pilih rentang lain.");
        }

        foreach ($requested as $nomor) {
            KeepNomorSurat::create([
                'signatory_id' => $validated['signatory'],
                'tanggal'      => $validated['tanggal'],
                'nomor'        => $nomor,
                'status'       => 'aktif',
            ]);
        }

        return redirect()
            ->route('keepnomorsurat')
            ->with('success', 'Nomor berhasil di-keep.');
    }

    public function gunakanKeepNomor(Request $request, $id)
    {
        $keep = KeepNomorSurat::with('signatory')->findOrFail($id);

        if ($keep->status !== 'aktif') {
            return back()->with('error', 'Nomor ini sudah terpakai.');
        }

        $validated = $request->validate([
            'perihal'     => 'required|string|max:255',
            'kode_tujuan' => 'required|exists:tujuan_surats,id',
            'klasifikasi' => 'required|exists:klasifikasi_surat,id',
        ]);

        $tujuan      = TujuanSurat::findOrFail($validated['kode_tujuan']);
        $klasifikasi = KlasifikasiSurat::findOrFail($validated['klasifikasi']);

        $nomorUrutPad     = str_pad($keep->nomor, 3, '0', STR_PAD_LEFT);
        $tanggalFormatted = $keep->tanggal->format('Ymd');

        $nomorSurat = "{$keep->signatory->kode}-{$tujuan->kode}-{$klasifikasi->kode}/{$tanggalFormatted}.{$nomorUrutPad}";

        DB::transaction(function () use ($validated, $keep, $tujuan, $klasifikasi, $nomorSurat) {

            RiwayatSurat::create([
                'perihal'              => $validated['perihal'],
                'penandatangan_id'     => $keep->signatory_id,
                'tujuan_surat_id'      => $tujuan->id,
                'klasifikasi_surat_id' => $klasifikasi->id,
                'tanggal'              => $keep->tanggal,
                'nomor_surat'          => $nomorSurat,
                'user_id'              => Auth::id(),
            ]);

            $keep->update(['status' => 'terpakai']);
        });

        return redirect()
            ->route('keepnomorsurat')
            ->with('success', "Surat berhasil dibuat dengan nomor {$nomorSurat}.")
            ->with('created_nomor', $nomorSurat);
    }

    public function cekNomorTerpakai(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
        ]);

        return response()->json([
            'used' => $this->usedNumbersForDate($request->tanggal),
        ]);
    }
}
