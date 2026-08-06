<?php

namespace App\Http\Controllers;

use App\Models\Kontrak;
use App\Models\Karyawan;
use App\Models\JenisKontrak;
use App\Models\Penandatangan;
use App\Models\Template;
use App\Services\DocxTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Carbon;
use App\Support\IndonesianDate;
use App\Support\BagianKontrak;
use Illuminate\Pagination\LengthAwarePaginator;
use ZipArchive;

class KontrakController extends Controller
{
    use NomorUrut;

    public function index(Request $request)
    {
        $search = trim((string) $request->search);
        $jenis  = $request->jenis;
        $bagian = $request->bagian;

        // Ambil semua kontrak (diurutkan tanggal terbaru dulu), lalu dikelompokkan
        // per karyawan di PHP - supaya karyawan yang punya lebih dari 1 riwayat
        // kontrak cuma nongol 1 baris (kontrak paling baru), sementara kontrak
        // lama tetap ada dan bisa dilihat lewat halaman detail (ikon mata).
        $semuaKontrak = Kontrak::with(['karyawan', 'jenisKontrak', 'penandatangan'])
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        $rows = collect();

        foreach ($semuaKontrak->groupBy('karyawan_id') as $riwayatKaryawan) {
            $terbaru     = $riwayatKaryawan->first();
            $riwayatLama = $riwayatKaryawan->slice(1)->values();

            // Filter berlaku ke seluruh riwayat karyawan itu (bukan cuma yang
            // terbaru) - jadi kalau salah satu kontraknya (lama ataupun baru)
            // cocok dengan pencarian/jenis/bagian, barisnya tetap ditampilkan.
            $cocokSearch = $search === '' || $riwayatKaryawan->contains(function ($k) use ($search) {
                return stripos((string) $k->nomor_kontrak, $search) !== false
                    || stripos((string) optional($k->karyawan)->nama, $search) !== false
                    || stripos((string) optional($k->karyawan)->nik, $search) !== false;
            });

            $cocokJenis = !$jenis || $riwayatKaryawan->contains(
                fn ($k) => (string) $k->jenis_kontrak_id === (string) $jenis
            );

            $cocokBagian = !$bagian || $riwayatKaryawan->contains(
                fn ($k) => strtolower(trim((string) $k->bagian_kontrak)) === strtolower(trim($bagian))
            );

            if ($cocokSearch && $cocokJenis && $cocokBagian) {
                $rows->push([
                    'kontrak'        => $terbaru,
                    'riwayat_lama'   => $riwayatLama,
                    'total_riwayat'  => $riwayatKaryawan->count(),
                ]);
            }
        }

        $rows = $rows->sortByDesc(fn ($r) => optional($r['kontrak']->tanggal)->timestamp)->values();

        $perPage    = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $kontrakList = new LengthAwarePaginator(
            $rows->forPage($currentPage, $perPage),
            $rows->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $jenisList  = JenisKontrak::orderBy('nama_jenis')->get();
        $bagianList = BagianKontrak::OPTIONS;

        if ($request->ajax()) {
            return view('kontrak.partials.results', compact('kontrakList'))->render();
        }

        return view('kontrak.index', compact('kontrakList', 'jenisList', 'bagianList'));
    }

    public function create()
    {
        $jenisList = JenisKontrak::orderBy('nama_jenis')->get();

        $tanggal = now()->toDateString();

        $nextSequence = $this->nextAvailableSequence($tanggal);

        $penandatangan = $this->resolveDefaultPenandatangan();

        return view('kontrak.create', compact('jenisList', 'nextSequence', 'penandatangan'));
    }

    public function store(Request $request, DocxTemplateService $docxService)
    {
        $request->validate([
            'karyawan_id'         => 'required|exists:karyawans,id',
            'jenis_kontrak_id'    => 'required|exists:jenis_kontraks,id',
            'tanggal'             => 'required|date',
            'nomor_urut'          => 'required|integer|min:1',
            'tanggal_mulai'       => 'required|date',
            'tanggal_selesai'     => 'nullable|date|after_or_equal:tanggal_mulai',
            'jabatan_kontrak'     => 'nullable|string|max:150',
            'bagian_kontrak'      => 'nullable|string|max:150',
            'rincian_pekerjaan_1' => 'nullable|string|max:255',
            'rincian_pekerjaan_2' => 'nullable|string|max:255',
            'rincian_pekerjaan_3' => 'nullable|string|max:255',
            'catatan'             => 'nullable|string',
        ]);

        $karyawan      = Karyawan::findOrFail($request->karyawan_id);
        $jenisKontrak  = JenisKontrak::findOrFail($request->jenis_kontrak_id);
        $penandatangan = $this->resolveDefaultPenandatangan();

        if (!$jenisKontrak->masa_giling && !$request->tanggal_selesai) {
            return back()->withInput()->with('error',
                'Tanggal Selesai wajib diisi untuk jenis kontrak ' . ($jenisKontrak->nama_singkat ?: $jenisKontrak->nama_jenis) . '.'
            );
        }

        $nomorInt = (int) $request->nomor_urut;

        $grouped = $this->groupedUsedNumbersForDate($request->tanggal);

        if (in_array($nomorInt, $grouped['terpakai'])) {
            return back()->withInput()->with('error',
                'Nomor #' . str_pad($nomorInt, 3, '0', STR_PAD_LEFT) . ' sudah dipakai untuk kontrak/surat lain.'
            );
        }

        if (in_array($nomorInt, $grouped['direservasi'])) {
            return back()->withInput()->with('error',
                'Nomor #' . str_pad($nomorInt, 3, '0', STR_PAD_LEFT) . ' sedang direservasi. Pilih nomor lain.'
            );
        }

        $urut = str_pad($nomorInt, 3, '0', STR_PAD_LEFT);

        $nomorKontrak = $this->buildNomorKontrak($jenisKontrak, $request->tanggal, $urut);
        $tanggalSelesai = $jenisKontrak->masa_giling ? null : $request->tanggal_selesai;

        // Template default aktif untuk jenis kontrak ini DIBEKUKAN ke kontrak
        // yang baru dibuat (disimpan sebagai template_id). Kalau nanti
        // default-nya diganti (upload template baru & jadikan default),
        // kontrak yang sudah ada ini TIDAK ikut berubah - hanya kontrak yang
        // dibuat SETELAH pergantian yang otomatis pakai template baru.
        $templateDefault = Template::where('jenis_kontrak_id', $jenisKontrak->id)
            ->where('is_default', true)
            ->first();

        $kontrak = Kontrak::create([
            'nomor_kontrak'        => $nomorKontrak,
            'tanggal'              => $request->tanggal,
            'karyawan_id'          => $karyawan->id,
            'jenis_kontrak_id'     => $jenisKontrak->id,
            'template_id'          => $templateDefault?->id,
            'penandatangan_id'     => $penandatangan?->id,
            'tanggal_mulai'        => $request->tanggal_mulai,
            'tanggal_selesai'      => $tanggalSelesai,
            'jabatan_kontrak'      => $request->jabatan_kontrak,
            'bagian_kontrak'       => $request->bagian_kontrak,
            'rincian_pekerjaan_1'  => $request->rincian_pekerjaan_1,
            'rincian_pekerjaan_2'  => $request->rincian_pekerjaan_2,
            'rincian_pekerjaan_3'  => $request->rincian_pekerjaan_3,
            'gaji_pokok'           => $jenisKontrak->gaji_pokok_default,
            'catatan'              => $request->catatan,
            'status'               => 'Draft',
            'user_id'              => Auth::id(),
        ]);

        try {
            $generatedPath = $this->generateDocument($kontrak, $docxService);
            $kontrak->update(['generated_file_path' => $generatedPath]);
        } catch (\Throwable $e) {
            report($e);
            return redirect()->route('kontrak.index')
                ->with('success', 'Kontrak tersimpan, tapi dokumen Word gagal dibuat otomatis: ' . $e->getMessage());
        }

        return redirect()->route('kontrak.index')
            ->with('success', 'Kontrak berhasil dibuat.')
            ->with('created_nomor', $nomorKontrak);
    }

    private function resolveDefaultPenandatangan(): ?Penandatangan
    {
        return Penandatangan::orderByRaw("
            CASE
                WHEN jabatan = 'General Manager' THEN 1
                WHEN jabatan = 'Manager' THEN 2
                WHEN jabatan = 'Asisten Manager' THEN 3
                ELSE 4
            END
        ")->first();
    }

    private function buildNomorKontrak(JenisKontrak $jenisKontrak, string $tanggal, string $urut): string
    {
        $prefix = config('kontrak.prefix', 'SG26-PERSE');
        $kodeNomor = $jenisKontrak->kode_nomor ?: $jenisKontrak->kode;

        return $prefix . '-' . $kodeNomor . '/' . date('Ymd', strtotime($tanggal)) . '.' . $urut;
    }

    private function generateDocument(Kontrak $kontrak, DocxTemplateService $docxService): string
    {
        $kontrak->load(['karyawan', 'jenisKontrak', 'penandatangan', 'template']);
        $karyawan = $kontrak->karyawan;
        $jenis    = $kontrak->jenisKontrak;
        $ttd      = $kontrak->penandatangan;

        // Urutan prioritas template: template yang sudah dipilih eksplisit
        // buat kontrak ini (lewat tombol "Ganti Template") > template
        // bawaan jenis kontrak > default_template.docx.
        $templatePath = $kontrak->template
            ? Storage::path($kontrak->template->file_path)
            : ($jenis->template_file
                ? Storage::path($jenis->template_file)
                : Storage::path('templates/kontrak/default_template.docx'));

        if (!file_exists($templatePath)) {
            $templatePath = Storage::path('templates/kontrak/default_template.docx');
        }

        $tempatTanggalLahir = $karyawan->tempat_tanggal_lahir ?: '-';

        $tanggalKontrak = Carbon::parse($kontrak->tanggal);
        $tanggalMulai   = Carbon::parse($kontrak->tanggal_mulai);
        $gajiPokok      = (int) ($kontrak->gaji_pokok ?? $jenis->gaji_pokok_default ?? 0);

        $data = [
            'NOMOR_KONTRAK'             => $kontrak->nomor_kontrak,
            'JENIS_KONTRAK'             => $jenis->nama_jenis,
            'TANGGAL_KONTRAK'           => $tanggalKontrak->translatedFormat('d F Y'),

            'PADA_HARI_INI'             => IndonesianDate::namaHari($tanggalKontrak),
            'TANGGAL_KONTRAK_TERBILANG' => IndonesianDate::tanggalTerbilang($tanggalKontrak),
            'BULAN_KONTRAK'             => IndonesianDate::namaBulan($tanggalKontrak),
            'TANGGAL_KONTRAK_SINGKAT'   => $tanggalKontrak->format('d-m-Y'),

            'NAMA_PENANDATANGAN'        => $ttd->nama ?? $ttd->jabatan ?? '-',
            'JABATAN_PENANDATANGAN'     => $ttd->jabatan ?? '-',
            'NO_SK_PENANDATANGAN'       => $ttd->no_sk ?? '-',
            'TANGGAL_SK_PENANDATANGAN'  => $ttd?->tanggal_sk
                ? Carbon::parse($ttd->tanggal_sk)->format('d-m-Y')
                : '-',

            'NAMA_KARYAWAN'             => $karyawan->nama,
            'NIK_KARYAWAN'              => $karyawan->nik,
            'NO_KTP_KARYAWAN'           => $karyawan->no_ktp ?? '-',
            'TEMPAT_TANGGAL_LAHIR'      => $tempatTanggalLahir,
            'JENIS_KELAMIN'             => $karyawan->jenis_kelamin ?? '-',
            'AGAMA'                     => $karyawan->agama ?? '-',
            'STATUS_PERKAWINAN'         => $karyawan->status_perkawinan ?? '-',
            'ALAMAT_KARYAWAN'           => $karyawan->alamat ?? '-',
            'JABATAN_KARYAWAN'          => $kontrak->jabatan_kontrak ?: '-',
            'DEPARTEMEN'                => $kontrak->bagian_kontrak ?: '-',
            'RINCIAN_PEKERJAAN_1'       => $kontrak->rincian_pekerjaan_1 ?: '-',
            'RINCIAN_PEKERJAAN_2'       => $kontrak->rincian_pekerjaan_2 ?: '-',
            'RINCIAN_PEKERJAAN_3'       => $kontrak->rincian_pekerjaan_3 ?: '-',

            'TANGGAL_MULAI'             => $tanggalMulai->translatedFormat('d F Y'),
            'TANGGAL_SELESAI'           => $kontrak->tanggal_selesai
                ? Carbon::parse($kontrak->tanggal_selesai)->translatedFormat('d F Y')
                : ($jenis->masa_giling ? 'Berakhirnya Masa Giling' : 'Tidak Ditentukan (Tetap)'),

            'GAJI_POKOK'                => number_format($gajiPokok, 0, ',', '.'),
            'GAJI_POKOK_TERBILANG'      => $gajiPokok > 0
                ? IndonesianDate::rupiahTerbilang($gajiPokok)
                : '-',
            'UPAH_LEMBUR_SEJAM'         => $gajiPokok > 0
                ? number_format((int) round($gajiPokok / 173), 0, ',', '.')
                : '-',

            'CATATAN'                   => $kontrak->catatan ?: '-',
        ];

        $outputRelative = 'kontrak/generated/' . str_replace(['/', '\\'], '-', $kontrak->nomor_kontrak) . '.docx';
        $outputPath = Storage::path($outputRelative);

        $docxService->generate($templatePath, $data, $outputPath);

        return $outputRelative;
    }

    public function show(Kontrak $kontrak)
    {
        $kontrak->load(['karyawan', 'jenisKontrak', 'penandatangan', 'user']);

        // Semua kontrak lain milik karyawan yang sama (riwayat lama & baru),
        // biar dari 1 halaman detail bisa keliatan semua kontrak dia.
        $riwayatLain = Kontrak::with(['jenisKontrak'])
            ->where('karyawan_id', $kontrak->karyawan_id)
            ->where('id', '!=', $kontrak->id)
            ->orderByDesc('tanggal')
            ->orderByDesc('id')
            ->get();

        return view('kontrak.show', compact('kontrak', 'riwayatLain'));
    }

    public function download(Kontrak $kontrak)
    {
        if (!$kontrak->generated_file_path || !Storage::exists($kontrak->generated_file_path)) {
            return back()->with('error', 'File dokumen kontrak belum tersedia.');
        }

        $kontrak->loadMissing('template');
        if ($kontrak->template && !$kontrak->template->published_at) {
            return back()->with('error', 'Template yang dipakai kontrak ini belum dipublish. Publish dulu template-nya di halaman Kelola Template.');
        }

        $filename = str_replace(['/', '\\'], '-', $kontrak->nomor_kontrak) . '.docx';

        return Storage::download($kontrak->generated_file_path, $filename);
    }

    public function preview(Kontrak $kontrak)
    {
        if (!$kontrak->generated_file_path || !Storage::exists($kontrak->generated_file_path)) {
            return back()->with('error', 'Dokumen belum digenerate, tidak bisa dipreview.');
        }

        $kontrak->load(['karyawan', 'jenisKontrak', 'penandatangan', 'template']);

        return view('kontrak.preview', [
            'kontrak' => $kontrak,
            'docxUrl' => route('kontrak.preview.file', $kontrak),
        ]);
    }

    /**
     * Kirim raw bytes file .docx (dipanggil via fetch() dari JS di halaman
     * preview, lalu di-render langsung di browser - bukan didownload).
     */
    public function previewFile(Kontrak $kontrak)
    {
        if (!$kontrak->generated_file_path || !Storage::exists($kontrak->generated_file_path)) {
            abort(404);
        }

        return response(Storage::get($kontrak->generated_file_path), 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'inline',
        ]);
    }

    /**
     * Tandai kontrak sebagai sudah dipublish. Setelah ini baru boleh didownload.
     */


    /**
     * Ganti template dokumen untuk kontrak ini, lalu generate ulang.
     * Publish status di-reset ke belum-publish karena isi dokumen berubah -
     * user wajib preview & publish ulang sebelum bisa download lagi.
     */
    public function switchTemplate(Request $request, Kontrak $kontrak, DocxTemplateService $docxService)
    {
        $request->validate([
            'template_id' => 'required|exists:templates,id',
        ]);

        $kontrak->update([
            'template_id'  => $request->template_id,
        ]);

        try {
            $generatedPath = $this->generateDocument($kontrak, $docxService);
            $kontrak->update(['generated_file_path' => $generatedPath]);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Gagal generate ulang dengan template baru: ' . $e->getMessage());
        }

        return redirect()->route('kontrak.show', $kontrak)
            ->with('success', 'Template berhasil diganti. Silakan preview lagi sebelum publish.');
    }

    // public function regenerate(Kontrak $kontrak, DocxTemplateService $docxService)

    public function regenerate(Request $request, Kontrak $kontrak, DocxTemplateService $docxService)
    {
        try {
            $generatedPath = $this->generateDocument($kontrak, $docxService);
            $kontrak->update(['generated_file_path' => $generatedPath]);
        } catch (\Throwable $e) {
            report($e);

            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat ulang dokumen: ' . $e->getMessage(),
                ], 422);
            }

            return back()->with('error', 'Gagal membuat ulang dokumen: ' . $e->getMessage());
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Dokumen kontrak berhasil dibuat ulang.',
            ]);
        }

        return back()->with('success', 'Dokumen kontrak berhasil dibuat ulang.');
    }

    public function uploadSignedForm(Kontrak $kontrak)
    {
        $kontrak->load(['karyawan', 'jenisKontrak', 'penandatangan']);

        return view('kontrak.upload', compact('kontrak'));
    }

    public function uploadSigned(Request $request, Kontrak $kontrak)
    {
        $request->validate([
            'file_kontrak' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $filePath = $request->file('file_kontrak')->store('kontrak-signed', 'public');

        $kontrak->update([
            'signed_file_path'    => $filePath,
            'signed_file_name'    => $request->file('file_kontrak')->getClientOriginalName(),
            'signed_uploaded_at'  => now(),
            'status'              => 'Aktif',
        ]);

        return redirect()->route('kontrak.upload.form', $kontrak->id)
            ->with('success', 'Kontrak yang sudah ditandatangani berhasil diunggah.');
    }

    public function deleteSigned(Kontrak $kontrak)
    {
        if ($kontrak->signed_file_path && Storage::disk('public')->exists($kontrak->signed_file_path)) {
            Storage::disk('public')->delete($kontrak->signed_file_path);
        }

        $kontrak->update([
            'signed_file_path'   => null,
            'signed_file_name'   => null,
            'signed_uploaded_at' => null,
            'status'             => 'Draft',
        ]);

        return redirect()->route('kontrak.upload.form', $kontrak->id)
            ->with('success', 'File tanda tangan berhasil dihapus.');
    }

    public function destroy(Kontrak $kontrak)
    {
        if ($kontrak->generated_file_path && Storage::exists($kontrak->generated_file_path)) {
            Storage::delete($kontrak->generated_file_path);
        }
        if ($kontrak->signed_file_path && Storage::disk('public')->exists($kontrak->signed_file_path)) {
            Storage::disk('public')->delete($kontrak->signed_file_path);
        }

        $kontrak->delete();

        return redirect()->route('kontrak.index')->with('success', 'Kontrak berhasil dihapus.');
    }

    /**
     * Cek status pembuatan dokumen untuk satu bagian/departemen - dipakai
     * frontend buat nampilin/nyembunyiin tombol "Buat Semua Dokumen" secara
     * otomatis pas dropdown Bagian diganti.
     */
    public function cekStatusBagian(Request $request)
    {
        $request->validate([
            'bagian' => 'required|string|in:' . implode(',', BagianKontrak::OPTIONS),
        ]);

        $bagianDicari = strtolower(trim($request->bagian));

        $semuaDiBagian = Kontrak::whereRaw('LOWER(TRIM(bagian_kontrak)) = ?', [$bagianDicari])->get();

        $belumGenerate = $semuaDiBagian->filter(function ($k) {
            return !$k->generated_file_path || !Storage::exists($k->generated_file_path);
        })->count();

        return response()->json([
            'total'          => $semuaDiBagian->count(),
            'belum_generate' => $belumGenerate,
            'sudah_generate' => $semuaDiBagian->count() - $belumGenerate,
        ]);
    }

    /**
     * Buat ulang/buat dokumen docx untuk semua kontrak di satu
     * bagian/departemen yang belum punya dokumen (atau file-nya hilang) -
     * dipanggil dari tombol "Buat Semua Dokumen" di Daftar Kontrak, biar
     * abis ini pengguna bisa langsung pakai "Unduh per Bagian" tanpa
     * harus membuat dokumennya satu-satu lewat halaman detail.
     */
    public function generateByBagian(Request $request, DocxTemplateService $docxService)
    {
        $request->validate([
            'bagian' => 'required|string|in:' . implode(',', BagianKontrak::OPTIONS),
        ]);

        $bagianDicari = strtolower(trim($request->bagian));

        $perluDigenerate = Kontrak::with(['karyawan', 'jenisKontrak', 'penandatangan'])
            ->whereRaw('LOWER(TRIM(bagian_kontrak)) = ?', [$bagianDicari])
            ->get()
            ->filter(function ($k) {
                return !$k->generated_file_path || !Storage::exists($k->generated_file_path);
            });

        if ($perluDigenerate->isEmpty()) {
            return response()->json([
                'success' => true,
                'berhasil' => 0,
                'gagal' => 0,
                'message' => 'Semua kontrak di bagian "' . $request->bagian . '" sudah punya dokumen.',
            ]);
        }

        $berhasil = 0;
        $gagalDetail = [];

        foreach ($perluDigenerate as $kontrak) {
            try {
                $generatedPath = $this->generateDocument($kontrak, $docxService);
                $kontrak->update(['generated_file_path' => $generatedPath]);
                $berhasil++;
            } catch (\Throwable $e) {
                report($e);
                $gagalDetail[] = $kontrak->nomor_kontrak;
            }
        }

        $gagal = count($gagalDetail);

        if ($berhasil === 0) {
            return response()->json([
                'success' => false,
                'berhasil' => 0,
                'gagal' => $gagal,
                'message' => 'Gagal membuat dokumen untuk semua (' . $gagal . ') kontrak di bagian ini. ' .
                    'Coba buat ulang manual lewat halaman detail kontrak.',
            ], 422);
        }

        $pesan = $berhasil . ' dokumen kontrak berhasil dibuat.';
        if ($gagal > 0) {
            $pesan .= ' ' . $gagal . ' kontrak gagal dibuat (' . implode(', ', $gagalDetail) . ') - cek detail kontraknya satu-satu.';
        }

        return response()->json([
            'success' => true,
            'berhasil' => $berhasil,
            'gagal' => $gagal,
            'message' => $pesan,
        ]);
    }

    /**
     * Unduh semua dokumen kontrak (docx) milik satu bagian/departemen
     * sekaligus, dibungkus jadi 1 file ZIP.
     */
    public function downloadByBagian(Request $request)
    {
        $request->validate([
            'bagian' => 'required|string|in:' . implode(',', BagianKontrak::OPTIONS),
        ]);

        // Pakai LOWER(TRIM(...)) biar cocok juga sama data lama yang mungkin
        // ada spasi nyasar / beda huruf besar-kecil (dulu field ini teks bebas).
        $bagianDicari = strtolower(trim($request->bagian));

        $semuaDiBagian = Kontrak::with('karyawan')
            ->whereRaw('LOWER(TRIM(bagian_kontrak)) = ?', [$bagianDicari])
            ->orderByDesc('tanggal')
            ->get();

        if ($semuaDiBagian->isEmpty()) {
            $pesan = 'Belum ada kontrak dengan bagian "' . $request->bagian . '". ' .
                'Cek lagi field Bagian/Departemen di data kontraknya - kalau kontrak dibuat sebelum ' .
                'dropdown bagian ini ada, isiannya bisa jadi teks bebas yang beda dari daftar sekarang.';

            return $request->ajax()
                ? response()->json(['success' => false, 'message' => $pesan], 422)
                : back()->with('error', $pesan);
        }

        $kontrakList = $semuaDiBagian->filter(fn ($k) => !empty($k->generated_file_path))->values();

        if ($kontrakList->isEmpty()) {
            $pesan = 'Ada ' . $semuaDiBagian->count() . ' kontrak untuk bagian "' . $request->bagian . '", ' .
                'tapi dokumen Word-nya belum dibuat. Buka detail kontraknya lalu klik "Buat Ulang Dokumen".';

            return $request->ajax()
                ? response()->json(['success' => false, 'message' => $pesan], 422)
                : back()->with('error', $pesan);
        }

        $zipName = 'Daftar Kontrak - ' . $request->bagian . '.zip';

        return $this->buildZipResponse($kontrakList, $zipName, $request);
    }

    /**
     * Unduh beberapa kontrak terpilih (lewat checkbox di halaman Riwayat
     * Kontrak) sekaligus, dibungkus jadi 1 file ZIP.
     */
    public function downloadSelected(Request $request)
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|exists:kontraks,id',
        ]);

        $kontrakList = Kontrak::with('karyawan')
            ->whereIn('id', $request->ids)
            ->whereNotNull('generated_file_path')
            ->orderByDesc('tanggal')
            ->get();

        if ($kontrakList->isEmpty()) {
            $pesan = 'Dokumen kontrak yang dipilih belum tersedia untuk diunduh.';

            return $request->ajax()
                ? response()->json(['success' => false, 'message' => $pesan], 422)
                : back()->with('error', $pesan);
        }

        return $this->buildZipResponse($kontrakList, 'Daftar Kontrak Terpilih.zip', $request);
    }

    /**
     * Bikin file ZIP sementara dari kumpulan kontrak, lalu kirim sebagai
     * download dan hapus file zip-nya setelah terkirim.
     */
    private function buildZipResponse($kontrakList, string $zipName, ?Request $request = null)
    {
        $tempPath = storage_path('app/tmp-' . uniqid('kontrak-zip-') . '.zip');

        $zip = new ZipArchive();
        if ($zip->open($tempPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            $pesan = 'Gagal membuat file ZIP.';

            return ($request && $request->ajax())
                ? response()->json(['success' => false, 'message' => $pesan], 500)
                : back()->with('error', $pesan);
        }

        $usedNames = [];

        foreach ($kontrakList as $kontrak) {
            if (!$kontrak->generated_file_path || !Storage::exists($kontrak->generated_file_path)) {
                continue;
            }

            $baseName = str_replace(['/', '\\'], '-', $kontrak->nomor_kontrak) . '.docx';

            // Hindari nama file dobel di dalam zip kalau ada nomor_kontrak yang sama.
            $entryName = $baseName;
            $suffix = 1;
            while (in_array($entryName, $usedNames, true)) {
                $entryName = str_replace('.docx', "-{$suffix}.docx", $baseName);
                $suffix++;
            }
            $usedNames[] = $entryName;

            $zip->addFile(Storage::path($kontrak->generated_file_path), $entryName);
        }

        $zip->close();

        return response()->download($tempPath, $zipName)->deleteFileAfterSend(true);
    }

    public function getNextSequence(Request $request)
    {
        // jenis_kontrak_id sengaja tidak lagi divalidasi wajib di sini -
        // nomor urut sekarang satu rangkaian gabungan (surat + semua jenis
        // kontrak), jadi tidak butuh info jenis kontrak buat dihitung.
        $request->validate([
            'tanggal' => 'required|date',
        ]);

        return response()->json([
            'sequence' => str_pad($this->nextAvailableSequence($request->tanggal), 3, '0', STR_PAD_LEFT),
        ]);
    }

    public function cekStatusNomor(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
        ]);

        return response()->json($this->groupedUsedNumbersForDate($request->tanggal));
    }
}
