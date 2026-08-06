<?php

namespace App\Http\Controllers;

use App\Exports\ArsipKasbonExport;
use App\Models\ArsipKasbon;
use App\Models\ArsipKasbonItem;
use App\Models\MasterAkun;
use App\Services\KasbonOcrService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ArsipKasbonController extends Controller
{
    public function index(Request $request)
    {
        $arsipList = $this->filteredQuery($request)
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            return view('arsipkasbon.partials.results', compact('arsipList'));
        }

        return view('arsipkasbon.index', compact('arsipList'));
    }
    public function export(Request $request)
    {
        $filters = $request->only(['q', 'tanggal_dari', 'tanggal_sampai']);
        $filename = 'arsip-spp-' . now()->format('Ymd-His') . '.xlsx';

        return Excel::download(new ArsipKasbonExport($filters), $filename);
    }

    protected function filteredQuery(Request $request): Builder
    {
        $query = ArsipKasbon::with('items')->latest();

        if ($request->filled('q')) {
            $keyword = trim($request->q);

            $query->where(function ($sub) use ($keyword) {
                $sub->where('document_no', 'like', "%{$keyword}%")
                    ->orWhere('numerator', 'like', "%{$keyword}%")
                    ->orWhere('nama_vendor', 'like', "%{$keyword}%")
                    ->orWhere('kode_vendor', 'like', "%{$keyword}%")
                    ->orWhere('park_oleh', 'like', "%{$keyword}%")
                    ->orWhere('cek_giro_trx', 'like', "%{$keyword}%")
                    ->orWhere('deskripsi_cost_object', 'like', "%{$keyword}%")
                    ->orWhere('terbilang', 'like', "%{$keyword}%")
                    ->orWhere('file_scan_name', 'like', "%{$keyword}%")
                    ->orWhere('jumlah_total', 'like', "%{$keyword}%")
                    ->orWhereRaw("DATE_FORMAT(tanggal_transaksi, '%d-%m-%Y') LIKE ?", ["%{$keyword}%"])
                    ->orWhereRaw("DATE_FORMAT(tanggal_transaksi, '%Y-%m-%d') LIKE ?", ["%{$keyword}%"])
                    ->orWhereHas('items', function ($item) use ($keyword) {
                        $item->where('no_akun', 'like', "%{$keyword}%")
                            ->orWhere('pk', 'like', "%{$keyword}%")
                            ->orWhere('cost_object', 'like', "%{$keyword}%")
                            ->orWhere('item_text', 'like', "%{$keyword}%");
                    });
            });
        }

        if ($request->filled('tanggal_dari')) {
            $query->whereDate('tanggal_transaksi', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $query->whereDate('tanggal_transaksi', '<=', $request->tanggal_sampai);
        }

        return $query;
    }

    public function create()
    {
        return view('arsipkasbon.create');
    }

    protected function findDuplicateDocument(string $documentNo, ?string $tanggalTransaksi = null, ?int $excludeId = null): ?ArsipKasbon
    {
        $query = ArsipKasbon::where('document_no', $documentNo);

        if (!empty($tanggalTransaksi)) {
            try {
                $year = Carbon::parse($tanggalTransaksi)->year;
                $query->whereYear('tanggal_transaksi', $year);
            } catch (\Exception $e) {
                // Tanggal tidak valid, biarkan fallback ke cek global di bawah.
            }
        }

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->first();
    }

    protected function duplicateMessage(string $documentNo, ?string $tanggalTransaksi = null): string
    {
        $year = null;
        if (!empty($tanggalTransaksi)) {
            try {
                $year = Carbon::parse($tanggalTransaksi)->year;
            } catch (\Exception $e) {
                // abaikan, tampilkan pesan tanpa tahun
            }
        }

        return $year
            ? "Surat Permintaan Pembayaran dengan No Dokumen \"{$documentNo}\" untuk tahun {$year} sudah pernah diunggah sebelumnya."
            : "Surat Permintaan Pembayaran dengan No Dokumen \"{$documentNo}\" sudah pernah diunggah sebelumnya.";
    }

    protected function buildScanFileName(?string $documentNo, ?string $tanggalTransaksi, string $extension): string
    {
        $year = null;
        if (!empty($tanggalTransaksi)) {
            try {
                $year = Carbon::parse($tanggalTransaksi)->year;
            } catch (\Exception $e) {
                // abaikan, lanjut tanpa tahun
            }
        }

        $base = !empty($documentNo) ? $documentNo : 'SPP-' . now()->format('YmdHis');

        // Ganti karakter selain huruf/angka/-/_ dengan "-" (mis. "/", spasi, ":")
        $base = preg_replace('/[^A-Za-z0-9\-_]+/', '-', $base);
        $base = trim($base, '-');

        if ($base === '') {
            $base = 'SPP-' . now()->format('YmdHis');
        }

        $name = $year ? "{$base}_{$year}" : $base;
        $extension = strtolower($extension) ?: 'bin';

        return "{$name}.{$extension}";
    }

    protected function uniqueStorageName(string $directory, string $filename, string $disk = 'public'): string
    {
        $dotPos = strrpos($filename, '.');
        $name = $dotPos !== false ? substr($filename, 0, $dotPos) : $filename;
        $ext = $dotPos !== false ? substr($filename, $dotPos) : '';

        $candidate = $filename;
        $counter = 2;

        while (Storage::disk($disk)->exists($directory . '/' . $candidate)) {
            $candidate = $name . '-' . $counter . $ext;
            $counter++;
        }

        return $candidate;
    }

    public function scan(Request $request, KasbonOcrService $ocr)
    {
        $request->validate([
            'file_scan' => 'required|file|mimes:jpg,jpeg,png,pdf|max:15360',
        ]);

        // Simpan sementara supaya bisa diproses OCR
        $tempPath = $request->file('file_scan')->store('kasbon-temp', 'local');
        $fullPath = Storage::disk('local')->path($tempPath);

        $result = $ocr->scan($fullPath);

        // Lengkapi deskripsi tiap item dari master_akun kalau kodenya sudah dikenal
        foreach ($result['items'] as &$item) {
            $master = MasterAkun::where('no_akun', $item['no_akun'])->first();
            $item['deskripsi_akun'] = $master?->deskripsi;
        }

        // Cek apakah document_no hasil bacaan OCR sudah pernah diarsipkan sebelumnya
        // di TAHUN yang sama (document_no boleh dipakai ulang kalau tahunnya beda).
        $duplicate = null;
        $documentNo = $result['header']['document_no'] ?? null;
        $tanggalTransaksi = $result['header']['tanggal_transaksi'] ?? null;
        if (!empty($documentNo)) {
            $existing = $this->findDuplicateDocument($documentNo, $tanggalTransaksi);
            if ($existing) {
                $duplicate = [
                    'document_no' => $documentNo,
                    'message'     => $this->duplicateMessage($documentNo, $tanggalTransaksi),
                    'existing_id' => $existing->id,
                ];
            }
        }

        return response()->json([
            'temp_path'   => $tempPath,
            'header'      => $result['header'],
            'items'       => $result['items'],
            'duplicate'   => $duplicate,
            'ocr_success' => $result['ocr_success'] ?? true,
        ]);
    }

    /**
     * AJAX: dipanggil setiap admin selesai ketik/koreksi Document No secara manual
     * di form verifikasi, untuk cek duplikat secara real-time.
     */
    public function checkDocumentNo(Request $request)
    {
        $documentNo = trim((string) $request->query('document_no', ''));
        $tanggalTransaksi = $request->query('tanggal_transaksi');

        if ($documentNo === '') {
            return response()->json(['duplicate' => false]);
        }

        $existing = $this->findDuplicateDocument($documentNo, $tanggalTransaksi);

        if (!$existing) {
            return response()->json(['duplicate' => false]);
        }

        return response()->json([
            'duplicate'   => true,
            'message'     => $this->duplicateMessage($documentNo, $tanggalTransaksi),
            'existing_id' => $existing->id,
        ]);
    }

    /**
     * Simpan final setelah admin verifikasi/koreksi hasil scan.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tanggal_transaksi'      => 'nullable|date',
            'document_no'            => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('arsip_kasbon', 'document_no')->where(function ($query) use ($request) {
                    if ($request->filled('tanggal_transaksi')) {
                        try {
                            $year = Carbon::parse($request->tanggal_transaksi)->year;
                            $query->whereYear('tanggal_transaksi', $year);
                        } catch (\Exception $e) {
                            // Tanggal tidak valid, biarkan fallback ke cek global.
                        }
                    }
                }),
            ],
            'numerator'               => 'nullable|string|max:50',
            'park_oleh'               => 'nullable|string|max:100',
            'nama_vendor'             => 'nullable|string|max:150',
            'kode_vendor'             => 'nullable|string|max:50',
            'cek_giro_trx'            => 'nullable|string|max:100',
            'deskripsi_cost_object'   => 'nullable|string|max:150',
            'jumlah_total'            => 'nullable|numeric',
            'terbilang'               => 'nullable|string',
            'temp_path'               => 'nullable|string',
            'file_scan'               => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:15360',
    
            'lampiran'                => 'nullable|array',
            'lampiran.*'              => 'file|mimes:jpg,jpeg,png,pdf|max:15360',

            'items'                   => 'required|array|min:1',
            'items.*.no_akun'         => 'required|string|max:30',
            'items.*.pk'              => 'nullable|string|max:20',
            'items.*.cost_object'     => 'nullable|string|max:50',
            'items.*.item_text'       => 'nullable|string',
            'items.*.jumlah_rupiah'   => 'nullable|numeric',
            'items.*.deskripsi_akun'  => 'nullable|string',
        ], [
            'document_no.unique' => 'Surat Permintaan Pembayaran dengan No Dokumen ":input" untuk tahun ini sudah pernah diunggah sebelumnya.',
        ]);

        DB::transaction(function () use ($request, $validated) {
            $finalPath = null;
            $finalName = null;

            if ($request->hasFile('file_scan')) {
                $uploadedFile = $request->file('file_scan');
                $extension = $uploadedFile->getClientOriginalExtension()
                    ?: $uploadedFile->extension()
                    ?: 'bin';

                $baseName = $this->buildScanFileName(
                    $validated['document_no'] ?? null,
                    $validated['tanggal_transaksi'] ?? null,
                    $extension
                );
                $finalName = $this->uniqueStorageName('kasbon', $baseName);
                $finalPath = $uploadedFile->storeAs('kasbon', $finalName, 'public');
            } elseif ($request->filled('temp_path') && Storage::disk('local')->exists($request->temp_path)) {
                $extension = pathinfo($request->temp_path, PATHINFO_EXTENSION) ?: 'png';

                $baseName = $this->buildScanFileName(
                    $validated['document_no'] ?? null,
                    $validated['tanggal_transaksi'] ?? null,
                    $extension
                );
                $finalName = $this->uniqueStorageName('kasbon', $baseName);
                $finalPath = 'kasbon/' . $finalName;

                Storage::disk('public')->put(
                    $finalPath,
                    Storage::disk('local')->get($request->temp_path)
                );
                Storage::disk('local')->delete($request->temp_path);
            }

            $kasbon = ArsipKasbon::create([
                'tanggal_transaksi'     => $validated['tanggal_transaksi'] ?? null,
                'document_no'           => $validated['document_no'] ?? null,
                'numerator'             => $validated['numerator'] ?? null,
                'park_oleh'             => $validated['park_oleh'] ?? null,
                'nama_vendor'           => $validated['nama_vendor'] ?? null,
                'kode_vendor'           => $validated['kode_vendor'] ?? null,
                'cek_giro_trx'          => $validated['cek_giro_trx'] ?? null,
                'deskripsi_cost_object' => $validated['deskripsi_cost_object'] ?? null,
                'jumlah_total'          => $validated['jumlah_total'] ?? null,
                'terbilang'             => $validated['terbilang'] ?? null,
                'file_scan'             => $finalPath,
                'file_scan_name'        => $finalName,
                'status'                => 'arsip',
                'user_id'               => Auth::id(),
            ]);

            foreach ($validated['items'] as $item) {
                $kasbon->items()->create([
                    'no_akun'       => $item['no_akun'],
                    'pk'            => $item['pk'] ?? null,
                    'cost_object'   => $item['cost_object'] ?? null,
                    'item_text'     => $item['item_text'] ?? null,
                    'jumlah_rupiah' => $item['jumlah_rupiah'] ?? 0,
                ]);

                // Perkaya "kamus" No Akun -> Deskripsi otomatis, supaya surat
                // berikutnya dengan akun yang sama langsung ke-autofill.
                if (!empty($item['no_akun']) && !empty($item['deskripsi_akun'])) {
                    MasterAkun::updateOrCreate(
                        ['no_akun' => $item['no_akun']],
                        ['deskripsi' => $item['deskripsi_akun']]
                    );
                }
            }

            // Simpan setiap file lampiran tambahan (kertas pendukung lain)
            foreach ($request->file('lampiran', []) as $file) {
                $path = $file->store('kasbon-lampiran', 'public');

                $kasbon->lampiran()->create([
                    'file_path' => $path,
                    'file_name' => $file->getClientOriginalName(),
                ]);
            }
        });

        return redirect()
            ->route('arsipkasbon.index')
            ->with('success', 'SPP berhasil diarsipkan.');
    }

    public function show(ArsipKasbon $arsipKasbon)
    {
        $arsipKasbon->load('items', 'lampiran');
        return view('arsipkasbon.show', ['kasbon' => $arsipKasbon]);
    }

    public function destroy(ArsipKasbon $arsipKasbon)
    {
        if ($arsipKasbon->file_scan && Storage::disk('public')->exists($arsipKasbon->file_scan)) {
            Storage::disk('public')->delete($arsipKasbon->file_scan);
        }
        $arsipKasbon->delete();

        return redirect()
            ->route('arsipkasbon.index')
            ->with('success', 'Arsip SPP berhasil dihapus.');
    }

    /**
     * AJAX: dipanggil tiap admin selesai ketik/scan No Akun di baris item,
     * untuk auto-fill deskripsi dari master_akun.
     */
    public function lookupAkun(string $noAkun)
    {
        $akun = MasterAkun::where('no_akun', $noAkun)->first();

        return response()->json([
            'found'     => (bool) $akun,
            'deskripsi' => $akun->deskripsi ?? null,
        ]);
    }
}