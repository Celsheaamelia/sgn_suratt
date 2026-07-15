<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RiwayatSurat;
use App\Models\Penandatangan;
use App\Models\TujuanSurat;
use App\Models\KlasifikasiSurat;
use App\Models\DetailSurat;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class RiwayatSuratController extends Controller
{
    public function index()
    {
        $suratList = RiwayatSurat::with([
            'penandatangan',
            'tujuanSurat',
            'klasifikasiSurat',
            'detailSurat'
        ])->latest()->get();

        $klasifikasiList = KlasifikasiSurat::orderBy('kode')->get();

        return view('riwayatsurat', compact(
            'suratList',
            'klasifikasiList'
        ));
        }

    public function show(RiwayatSurat $riwayatSurat)
    {
        $riwayatSurat->load(['penandatangan', 'tujuanSurat', 'klasifikasiSurat', 'user']);

        return view('surat.detail', [
            'surat' => $riwayatSurat,
        ]);
    }

    public function create()
    {
       $penandatanganList = Penandatangan::orderByRaw("
    CASE
        WHEN jabatan = 'General Manager' THEN 1
        WHEN jabatan = 'Manager' THEN 2
        WHEN jabatan = 'Asisten Manager' THEN 3
        ELSE 4
    END
")->get();
        $tujuanList = TujuanSurat::orderBy('nama_tujuan')->get();
        $klasifikasiList = KlasifikasiSurat::orderBy('jenis_surat')->get();

        // $today = now()->toDateString();
        $tanggal = now()->toDateString();
        $nextSequence = RiwayatSurat::whereDate('tanggal', $tanggal)->count() + 1;

        $suratList = RiwayatSurat::latest()->get();

        return view('tambahsurat', compact(
            'penandatanganList',
            'tujuanList',
            'klasifikasiList',
            'nextSequence',
            'suratList'
        ));
    }

    public function store(Request $request)
{
    $request->validate([
        'perihal' => 'required',
        'signatory' => 'required',
        'kode_tujuan' => 'required',
        'klasifikasi' => 'required',
        'tanggal' => 'required|date|date_equals:today',
    ]);

    $jumlahHariIni = RiwayatSurat::whereDate('tanggal', $request->tanggal)->count();
    $urut = str_pad($jumlahHariIni + 1, 3, '0', STR_PAD_LEFT);

    // Ambil data berdasarkan ID yang dipilih di form
    $penandatangan = Penandatangan::find($request->signatory);
    $tujuan = TujuanSurat::find($request->kode_tujuan);
    $klasifikasi = KlasifikasiSurat::find($request->klasifikasi);

    // Format baru: PENANDATANGAN-TUJUAN-KLASIFIKASI/YYYYMMDD.SEQ
    // Contoh: SG26-BD05-SKP/20260710.004
    $nomor = $penandatangan->kode . '-' .
            $tujuan->kode . '-' .
            $klasifikasi->kode . '/' .
            date('Ymd', strtotime($request->tanggal)) . '.' .
            $urut;

    RiwayatSurat::create([
        'nomor_surat' => $nomor,
        'perihal' => $request->perihal,
        'tanggal' => $request->tanggal,
        'penandatangan_id' => $request->signatory,
        'tujuan_surat_id' => $request->kode_tujuan,
        'klasifikasi_surat_id' => $request->klasifikasi,
        'user_id' => Auth::id(),
    ]);

    return redirect()->route('tambahsurat')
        ->with('success', 'Surat berhasil dibuat.')
        ->with('created_nomor', $nomor);
}

    public function showUpload($id)
    {
        $surat = RiwayatSurat::with([
            'klasifikasiSurat',
            'tujuanSurat',
            'penandatangan',
            'detailSurat'   // tambahkan ini
        ])->findOrFail($id);

        return view('suratupload', compact('surat'));
    }

    public function storeUpload(Request $request, $id)
    {
        $request->validate([
            'file_surat' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $surat = RiwayatSurat::findOrFail($id);

        $filePath = $request->file('file_surat')->store('surat', 'public');

        DetailSurat::updateOrCreate(
            [
                'riwayatsurat_id' => $surat->id,
            ],
            [
                'file_path'   => $filePath,
                'file_name'   => $request->file('file_surat')->getClientOriginalName(),
                'uploaded_at' => now(),
            ]
        );

        $surat->update([
            'status' => 'Terupload',
        ]);

        return redirect()
            ->route('surat.upload.show', $surat->id)
            ->with('success', 'Surat berhasil diupload.');
    }


    public function deleteUpload($id)
    {
        $surat = RiwayatSurat::with('detailSurat')->findOrFail($id);
        if ($surat->detailSurat) {
            if (Storage::disk('public')->exists($surat->detailSurat->file_path)) {
                Storage::disk('public')->delete($surat->detailSurat->file_path);
            }
            $surat->detailSurat()->delete();
        }
        $surat->update([
            'status' => 'Belum Terupload',
        ]);
        return redirect()
            ->route('surat.upload.show', $surat->id)
            ->with('success', 'File berhasil dihapus.');
    }

    public function getNextSequence(Request $request)
    {
        $tanggal = $request->tanggal;

        $jumlah = RiwayatSurat::whereDate('tanggal', $tanggal)->count();

        return response()->json([
            'sequence' => str_pad($jumlah + 1, 3, '0', STR_PAD_LEFT)
        ]);
    }
}
