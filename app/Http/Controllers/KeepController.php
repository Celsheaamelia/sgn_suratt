<?php

namespace App\Http\Controllers;

use App\Models\RiwayatSurat;
use App\Models\Penandatangan;
use App\Models\TujuanSurat;
use App\Models\KlasifikasiSurat;
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

        $tujuanList      = TujuanSurat::orderBy('kode')->get();
        $klasifikasiList = KlasifikasiSurat::orderBy('kode')->get();

        $keepList = RiwayatSurat::with(['penandatangan', 'tujuanSurat', 'klasifikasiSurat'])
            ->where('status', 'Direservasi')
            ->orderByDesc('tanggal')
            ->orderBy('nomor_surat')
            ->get();

        $tanggal      = now()->toDateString();
        $nextSequence = $this->nextAvailableSequence($tanggal);

        return view('keepnomorsurat', compact(
            'penandatanganList',
            'tujuanList',
            'klasifikasiList',
            'keepList',
            'nextSequence'
        ));
    }

    public function storeKeepNomor(Request $request)
    {
        $validated = $request->validate([
            'signatory'   => 'required|exists:penandatangan,id',
            'kode_tujuan' => 'required|exists:tujuan_surats,id',
            'klasifikasi' => 'required|exists:klasifikasi_surat,id',
            'tanggal'     => 'required|date',
            'perihal'     => 'required|string|max:255',
            'nomor_awal'  => 'required|integer|min:1',
            'nomor_akhir' => 'required|integer|min:1|gte:nomor_awal',
        ]);

        $requested = range($validated['nomor_awal'], $validated['nomor_akhir']);
        $grouped   = $this->groupedUsedNumbersForDate($validated['tanggal']);

        $conflictTerpakai    = array_values(array_intersect($requested, $grouped['terpakai']));
        $conflictDireservasi = array_values(array_intersect($requested, $grouped['direservasi']));

        if (!empty($conflictTerpakai) || !empty($conflictDireservasi)) {
            $messages = [];

            if (!empty($conflictTerpakai)) {
                $list = implode(', #', array_map(fn ($n) => str_pad($n, 3, '0', STR_PAD_LEFT), $conflictTerpakai));
                $messages[] = "Nomor #{$list} sudah dipakai (sudah jadi surat).";
            }

            if (!empty($conflictDireservasi)) {
                $list = implode(', #', array_map(fn ($n) => str_pad($n, 3, '0', STR_PAD_LEFT), $conflictDireservasi));
                $messages[] = "Nomor #{$list} sudah di-keep oleh orang lain.";
            }

            return back()
                ->withInput()
                ->with('error', implode(' ', $messages) . ' Pilih rentang lain.');
        }

        $penandatangan = Penandatangan::findOrFail($validated['signatory']);
        $tujuan        = TujuanSurat::findOrFail($validated['kode_tujuan']);
        $klasifikasi   = KlasifikasiSurat::findOrFail($validated['klasifikasi']);

        $tanggalFormatted = date('Ymd', strtotime($validated['tanggal']));

        DB::transaction(function () use ($requested, $validated, $penandatangan, $tujuan, $klasifikasi, $tanggalFormatted) {
            foreach ($requested as $nomor) {
                $urutPad = str_pad($nomor, 3, '0', STR_PAD_LEFT);

                $nomorSurat = $penandatangan->kode . '-' .
                    $tujuan->kode . '-' .
                    $klasifikasi->kode . '/' .
                    $tanggalFormatted . '.' .
                    $urutPad;

                RiwayatSurat::create([
                    'nomor_surat'          => $nomorSurat,
                    'perihal'              => $validated['perihal'],
                    'tanggal'              => $validated['tanggal'],
                    'penandatangan_id'     => $penandatangan->id,
                    'tujuan_surat_id'      => $tujuan->id,
                    'klasifikasi_surat_id' => $klasifikasi->id,
                    'user_id'              => Auth::id(),
                    'status'               => 'Direservasi',
                ]);
            }
        });

        $jumlah = count($requested);

        $tanggalFormatted2 = date('Ymd', strtotime($validated['tanggal']));
        $createdNumbers = collect($requested)->map(function ($nomor) use ($penandatangan, $tujuan, $klasifikasi, $tanggalFormatted2) {
            return $penandatangan->kode . '-' .
                $tujuan->kode . '-' .
                $klasifikasi->kode . '/' .
                $tanggalFormatted2 . '.' .
                str_pad($nomor, 3, '0', STR_PAD_LEFT);
        })->all();

        return redirect()
            ->route('keepnomorsurat')
            ->with('success', "{$jumlah} nomor berhasil dicadangkan.")
            ->with('created_numbers', $createdNumbers);
    }

    public function cancelKeepNomor($id)
    {
        $surat = RiwayatSurat::findOrFail($id);

        if ($surat->status !== 'Direservasi') {
            return back()->with('error', 'Nomor ini sudah terpakai dan tidak bisa dibatalkan.');
        }

        $nomor = $surat->nomor_surat;
        $surat->delete();

        return redirect()
            ->route('keepnomorsurat')
            ->with('success', "cadangan nomor {$nomor} berhasil dibatalkan.");
    }

    public function cekNomorTerpakai(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
        ]);

        return response()->json(
            $this->groupedUsedNumbersForDate($request->tanggal)
        );
    }
}
