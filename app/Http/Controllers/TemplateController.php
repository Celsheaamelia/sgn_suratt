<?php

namespace App\Http\Controllers;

use App\Models\JenisKontrak;
use App\Models\Template;
use App\Services\DocxTemplateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TemplateController extends Controller
{
    /**
     * Placeholder yang WAJIB ada di setiap template - dicek ulang setiap kali
     * admin upload (baik template baru maupun versi hasil edit manual di
     * Word). Kalau ada yang hilang/typo pas admin edit pasal, upload ditolak
     * di sini - supaya ketauan dari awal, bukan pas kontrak beneran sudah
     * digenerate dan hasilnya masih ada "{{NAMA_KARYAWAN}}" mentah di dokumen.
     *
     * SENGAJA TIDAK WAJIB (boleh dihapus/diganti admin jadi teks tetap):
     * - CATATAN                          -> sering memang dikosongin
     * - NO_SK_PENANDATANGAN,
     *   TANGGAL_SK_PENANDATANGAN         -> sama utk banyak kontrak, admin
     *                                      pilih atur manual per-template
     * - GAJI_POKOK, GAJI_POKOK_TERBILANG,
     *   UPAH_LEMBUR_SEJAM                -> disepakati sama rata per jenis
     *                                      kontrak, admin atur manual (HATI-
     *                                      HATI: update ketiganya BARENGAN
     *                                      kalau gaji berubah, biar konsisten)
     * - JENIS_KONTRAK, TANGGAL_KONTRAK   -> template produksi ternyata gak
     *                                      pernah pakai ini, cukup pakai
     *                                      versi pecahnya (PADA_HARI_INI dkk)
     * - NIK_KARYAWAN                     -> dokumen legal cuma nampilin No.
     *                                      KTP, NIK internal gak ditampilkan
     * - TANGGAL_SELESAI                  -> template PKWT DMG (masa giling)
     *                                      tidak pakai tanggal pasti, ditulis
     *                                      manual "sampai berakhirnya Masa
     *                                      Giling". CATATAN buat template
     *                                      non-masa-giling (LMG/KTR): tetap
     *                                      SERTAKAN placeholder ini di
     *                                      template kalian, sistem cuma gak
     *                                      MEWAJIBKAN, bukan berarti gak
     *                                      perlu dipakai kalau memang jenis
     *                                      kontraknya butuh tanggal selesai.
     */
    private const REQUIRED_PLACEHOLDERS = [
        'NOMOR_KONTRAK',
        'PADA_HARI_INI',
        'TANGGAL_KONTRAK_TERBILANG',
        'BULAN_KONTRAK',
        'TANGGAL_KONTRAK_SINGKAT',
        'NAMA_KARYAWAN',
        'NO_KTP_KARYAWAN',
        'TEMPAT_TANGGAL_LAHIR',
        'JENIS_KELAMIN',
        'AGAMA',
        'STATUS_PERKAWINAN',
        'ALAMAT_KARYAWAN',
        'JABATAN_KARYAWAN',
        'DEPARTEMEN',
        'RINCIAN_PEKERJAAN_1',
        'RINCIAN_PEKERJAAN_2',
        'RINCIAN_PEKERJAAN_3',
        'TANGGAL_MULAI',
    ];

    /**
     * Halaman "Kelola Template": daftar semua template dikelompokkan per
     * jenis kontrak, masing-masing bisa diupload baru / dihapus / dijadikan
     * default / dipreview.
     */
    public function index()
    {
        $jenisList = JenisKontrak::orderBy('nama_jenis')->get();

        $templatesByJenis = Template::orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('jenis_kontrak_id');

        return view('template-kontrak.index', compact('jenisList', 'templatesByJenis'));
    }

    /**
     * Upload template docx baru untuk suatu jenis kontrak.
     */
    public function store(Request $request, DocxTemplateService $docxService)
    {
        $request->validate([
            'jenis_kontrak_id' => 'required|exists:jenis_kontraks,id',
            'nama_template'    => 'required|string|max:150',
            'file'             => 'required|file|mimes:docx|max:10240',
            'is_default'       => 'nullable|boolean',
        ]);

        $jenis = JenisKontrak::findOrFail($request->jenis_kontrak_id);

        $filename = 'tpl-' . $jenis->id . '-' . now()->format('YmdHis') . '-' . uniqid() . '.docx';
        $path = $request->file('file')->storeAs('templates/kontrak', $filename);

        // Cek placeholder wajib masih lengkap - jaga-jaga kalau pas admin
        // edit pasal manual di Word, ada placeholder yang gak sengaja
        // kehapus / typo / ketimpa.
        $placeholderDitemukan = $docxService->extractPlaceholders(Storage::path($path));
        $placeholderHilang = array_values(array_diff(self::REQUIRED_PLACEHOLDERS, $placeholderDitemukan));

        if (!empty($placeholderHilang)) {
            Storage::delete($path); // batalkan, jangan simpan file yang rusak

            return back()->withInput()->with('error',
                'Upload ditolak - placeholder berikut hilang atau berubah di file yang diupload: '
                . implode(', ', array_map(fn ($p) => '{{' . $p . '}}', $placeholderHilang))
                . '. Cek lagi file Word-nya, pastikan placeholder ditulis persis (huruf besar semua, tanpa spasi/typo), jangan sampai ikut kehapus/keketik ulang.'
            );
        }

        $jadikanDefault = $request->boolean('is_default');

        // Kalau ini template PERTAMA buat jenis kontrak ini, otomatis
        // dijadikan default - biar kontrak baru selalu ada template aktif.
        if (!$jadikanDefault && !Template::where('jenis_kontrak_id', $jenis->id)->exists()) {
            $jadikanDefault = true;
        }

        if ($jadikanDefault) {
            Template::where('jenis_kontrak_id', $jenis->id)->update(['is_default' => false]);
        }

        Template::create([
            'jenis_kontrak_id' => $jenis->id,
            'nama_template'    => $request->nama_template,
            'file_path'        => $path,
            'is_default'       => $jadikanDefault,
        ]);

        return back()->with('success', 'Template berhasil diupload'
            . ($jadikanDefault ? ' dan dijadikan default. Kontrak baru untuk jenis ini akan memakai template tersebut.' : '.'));
    }

    /**
     * Tandai template sebagai published - kontrak yang memakai template ini
     * baru bisa didownload setelah ini. Template baru diupload selalu mulai
     * dari status Draft (published_at kosong) supaya admin sempat cek dulu
     * (preview) sebelum dianggap siap dipakai untuk dokumen resmi.
     */
    public function publish(Template $template)
    {
        $template->update(['published_at' => now()]);

        return back()->with('success', '"' . $template->nama_template . '" berhasil dipublish. Kontrak yang memakai template ini sekarang bisa didownload.');
    }

    /**
     * Jadikan template yang sudah ada sebagai default (tanpa upload baru).
     */
    public function setDefault(Template $template)
    {
        Template::where('jenis_kontrak_id', $template->jenis_kontrak_id)->update(['is_default' => false]);
        $template->update(['is_default' => true]);

        return back()->with('success', '"' . $template->nama_template . '" dijadikan template default. Kontrak baru akan memakai template ini.');
    }

    /**
     * Hapus template. Kontrak lama yang sudah terlanjur pakai template ini
     * tidak terpengaruh isinya (dokumen yang sudah digenerate tetap ada di
     * storage), cuma referensinya otomatis lepas (template_id jadi null).
     */
    public function destroy(Template $template)
    {
        if (Storage::exists($template->file_path)) {
            Storage::delete($template->file_path);
        }

        $wasDefault = $template->is_default;
        $jenisId = $template->jenis_kontrak_id;

        $template->delete();

        // Kalau yang dihapus itu default dan masih ada template lain buat
        // jenis kontrak ini, otomatis jadiin salah satunya default lagi -
        // supaya kontrak baru tetap ada template aktif, bukan kosong.
        if ($wasDefault) {
            $next = Template::where('jenis_kontrak_id', $jenisId)->latest()->first();
            if ($next) {
                $next->update(['is_default' => true]);
            }
        }

        return back()->with('success', 'Template berhasil dihapus.');
    }

    /**
     * Download file template mentah (yang masih berisi placeholder
     * {{...}}) - dipakai admin sebagai bahan edit pasal/redaksional di
     * Word, lalu diupload lagi lewat form yang sama.
     */
    public function download(Template $template)
    {
        if (!Storage::exists($template->file_path)) {
            return back()->with('error', 'File template tidak ditemukan di storage.');
        }

        $filename = \Illuminate\Support\Str::slug($template->nama_template) . '.docx';

        return Storage::download($template->file_path, $filename);
    }

    /**
     * Preview isi template MENTAH (placeholder {{...}} belum keisi data
     * karyawan, memang begitu adanya) - dipakai buat cek pasal / layout
     * sebelum dipakai generate kontrak beneran.
     */
    public function preview(Template $template)
    {
        if (!Storage::exists($template->file_path)) {
            return back()->with('error', 'File template tidak ditemukan di storage.');
        }

        return view('template-kontrak.preview', [
            'template' => $template,
            'docxUrl'  => route('kontrak-template.preview.file', $template),
        ]);
    }

    public function previewFile(Template $template)
    {
        if (!Storage::exists($template->file_path)) {
            abort(404);
        }

        return response(Storage::get($template->file_path), 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'Content-Disposition' => 'inline',
        ]);
    }
}
