<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class KasbonOcrService
{
    /** Urutan field header yang dipakai bareng di beberapa method di bawah. */
    private const HEADER_FIELDS = [
        'tanggal_transaksi', 'document_no', 'numerator', 'park_oleh', 'nama_vendor',
        'kode_vendor', 'cek_giro_trx', 'deskripsi_cost_object',
        'jumlah_total', 'terbilang',
    ];

    public function __construct(protected GeminiService $gemini)
    {
    }

    /**
     * Baca 1 file gambar/scan SPP pakai Gemini vision (structured output),
     * lalu balikin field-field header + baris item siap pakai buat isi
     * form verifikasi di halaman "Scan Surat Baru".
     *
     * Return array:
     * [
     *   'raw_text' => string,          // JSON mentah dari Gemini, buat debug
     *   'header'   => [...],           // field header yang berhasil ketebak
     *   'items'    => [ [...], ... ],  // baris item yang berhasil ketebak
     * ]
     */
    public function scan(string $imagePath): array
    {
        try {
            $parsed = $this->gemini->generateStructuredFromImage(
                $imagePath,
                $this->buildPrompt(),
                $this->buildSchema()
            );
        } catch (\Throwable $e) {
            Log::warning('Gemini OCR gagal: ' . $e->getMessage());
            return $this->emptyResult();
        }

        $header = $this->normalizeHeader($parsed['header'] ?? []);
        $items  = $this->normalizeItems($parsed['items'] ?? []);
        $dokumenTerbaca = filter_var($parsed['dokumen_terbaca'] ?? true, FILTER_VALIDATE_BOOLEAN);

        return [
            'raw_text' => json_encode($parsed, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            'header'   => $header,
            'items'    => $items,
            // Utamakan penilaian Gemini sendiri (dokumen_terbaca) karena dia yang lihat
            // gambarnya -- lebih akurat daripada nebak dari kosong-tidaknya field, soalnya
            // Gemini kadang tetap ngisi field "required" walau gambarnya salah/ngawur/blur.
            // Sebagai jaring pengaman tambahan, tetap dianggap gagal kalau field-field inti
            // (No Dokumen, Tanggal Transaksi, Jumlah Total) sama sekali gak ada satupun yang
            // kebaca, walau Gemini bilang "terbaca".
            'ocr_success' => $dokumenTerbaca && $this->hasCoreValue($header),
        ];
    }

    /** Cek apakah minimal salah satu field INTI (paling penting buat arsip) berhasil kebaca. */
    private function hasCoreValue(array $header): bool
    {
        foreach (['document_no', 'tanggal_transaksi', 'jumlah_total'] as $f) {
            $val = $header[$f] ?? null;
            if ($val !== null && $val !== '') {
                return true;
            }
        }
        return false;
    }

    /**
     * Instruksi ke Gemini soal cara membaca form SPP. Struktur JSON-nya sendiri
     * sudah dipaksa lewat responseSchema, jadi prompt ini fokus ke ATURAN
     * pembacaan datanya saja, bukan format output.
     */
    private function buildPrompt(): string
    {
        return <<<PROMPT
Kamu membaca gambar formulir "Surat Permintaan Pembayaran" (SPP) / kasbon berbahasa Indonesia.

Panduan membaca tiap field:
- tanggal_transaksi: format asli biasanya dd.mm.yyyy atau dd/mm/yyyy -- konversi ke YYYY-MM-DD.
- document_no: nomor dokumen SAP di kanan atas, biasanya di sebelah label "Document No", angka panjang
    sekitar 10 digit (contoh: 1900031757).
- numerator: nomor urut CETAK/CAP MESIN yang letaknya di bagian ATAS TENGAH kertas, biasanya format
    campuran huruf+angka atau angka sekitar 6-8 karakter, dicetak dengan font mesin ketik/dot-matrix
    (contoh: 2407009). Ini BUKAN document_no dan BUKAN Posting Oleh/Park Oleh -- ini nomor urut arsip
    fisik yang biasanya berdiri sendiri, terpisah dari blok "Tanggal Transaksi / Document No / Posting
    Oleh / Park Oleh" di kanan atas. Kalau ragu antara numerator dan document_no, numerator adalah yang
    posisinya paling atas/tengah dan document_no adalah yang ada label "Document No" di sampingnya.
- park_oleh: nama petugas yang membuat/park dokumen. Kalau tidak ada, isi "Administrator".
- nama_vendor, kode_vendor: identitas vendor/penerima pembayaran.
- cek_giro_trx: nomor Cek/Giro/Trx bank.
- deskripsi_cost_object: uraian peruntukan dana / cost object.
- jumlah_total: total nilai uang, angka murni tanpa titik/koma pemisah ribuan.
- terbilang: kalimat pembilang nominal total (contoh: "Satu Juta Rupiah").
- items: baris-baris tabel rincian akun di bagian bawah form. Tiap baris:
    - no_akun: kode akun (biasanya 6-8 digit angka)
    - pk: kode PK (biasanya 1-3 digit angka), kosongkan jika tidak ada
    - cost_object: cost object baris tersebut jika ada
    - item_text: deskripsi/uraian item
    - jumlah_rupiah: nilai uang baris tersebut, angka murni

Kalau sebuah field benar-benar tidak terbaca atau tidak ada di gambar, kosongkan string-nya
(jangan mengarang nilai). Kalau tidak ada baris item sama sekali, kembalikan array items kosong.

PENTING -- penilaian "dokumen_terbaca":
Selain field-field di atas, kamu WAJIB isi juga field "dokumen_terbaca" (true/false) yang menilai
apakah gambar ini BENAR-BENAR bisa dibaca sebagai form SPP/kasbon yang valid. Isi FALSE kalau salah
satu dari ini terjadi:
- Gambar buram/blur, gelap, terlalu kecil, terpotong, atau kualitasnya terlalu jelek untuk dibaca
    dengan yakin.
- Gambar yang diupload BUKAN form SPP/kasbon sama sekali (misal foto KTP, struk belanja, foto orang,
    dokumen lain yang tidak nyambung, atau gambar acak/ngawur).
- Kamu terpaksa MENGARANG atau MENEBAK nilai document_no, tanggal_transaksi, atau jumlah_total karena
    sebenarnya tidak kelihatan jelas di gambar -- lebih baik jujur isi FALSE daripada mengarang.
Isi TRUE hanya kalau kamu yakin gambar ini benar form SPP/kasbon asli dan minimal document_no,
tanggal_transaksi, atau jumlah_total kelihatan cukup jelas untuk dibaca dengan percaya diri.
PROMPT;
    }

    /**
     * JSON schema (subset OpenAPI) yang dikirim ke Gemini lewat generationConfig.responseSchema.
     * Ini yang MEMAKSA Gemini balikin struktur JSON persis seperti ini -- teknik yang sama
     * dipakai prototipe "auto scan", diterapkan lewat REST API karena Laravel tidak pakai SDK JS.
     */
    private function buildSchema(): array
    {
        $stringField = ['type' => 'string'];

        return [
            'type' => 'object',
            'properties' => [
                'dokumen_terbaca' => ['type' => 'boolean'],
                'header' => [
                    'type' => 'object',
                    'properties' => [
                        'tanggal_transaksi'      => $stringField,
                        'document_no'            => $stringField,
                        'numerator'               => $stringField,
                        'park_oleh'               => $stringField,
                        'nama_vendor'             => $stringField,
                        'kode_vendor'             => $stringField,
                        'cek_giro_trx'            => $stringField,
                        'deskripsi_cost_object'   => $stringField,
                        'jumlah_total'            => ['type' => 'number'],
                        'terbilang'               => $stringField,
                    ],
                    'required' => [
                        'tanggal_transaksi', 'document_no', 'numerator', 'park_oleh', 'nama_vendor',
                        'kode_vendor', 'cek_giro_trx', 'deskripsi_cost_object',
                        'jumlah_total', 'terbilang',
                    ],
                ],
                'items' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'no_akun'       => $stringField,
                            'pk'            => $stringField,
                            'cost_object'   => $stringField,
                            'item_text'     => $stringField,
                            'jumlah_rupiah' => ['type' => 'number'],
                        ],
                        'required' => ['no_akun', 'pk', 'cost_object', 'item_text', 'jumlah_rupiah'],
                    ],
                ],
            ],
            'required' => ['dokumen_terbaca', 'header', 'items'],
        ];
    }

    private function normalizeHeader(array $h): array
    {
        $result = [];
        foreach (self::HEADER_FIELDS as $f) {
            $val = $h[$f] ?? null;
            $result[$f] = ($val === '' || $val === null) ? null : $val;
        }

        if (!empty($result['jumlah_total'])) {
            $result['jumlah_total'] = $this->toNumber($result['jumlah_total']);
        }

        if (!empty($result['tanggal_transaksi']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $result['tanggal_transaksi'])) {
            $result['tanggal_transaksi'] = null;
        }

        // Numerator kadang kebaca dengan spasi nyasar (mis. "24 07009") -- rapikan.
        if (!empty($result['numerator'])) {
            $result['numerator'] = trim(preg_replace('/\s+/', '', $result['numerator']));
        }

        return $result;
    }

    private function normalizeItems(array $items): array
    {
        $result = [];
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $noAkun = trim((string) ($item['no_akun'] ?? ''));
            if ($noAkun === '') {
                continue; // baris kosong, abaikan
            }

            $result[] = [
                'no_akun'       => $noAkun,
                'pk'            => $this->nullIfEmpty($item['pk'] ?? null),
                'cost_object'   => $this->nullIfEmpty($item['cost_object'] ?? null),
                'item_text'     => $this->nullIfEmpty($item['item_text'] ?? null),
                'jumlah_rupiah' => isset($item['jumlah_rupiah']) ? $this->toNumber($item['jumlah_rupiah']) : null,
            ];
        }
        return $result;
    }

    private function nullIfEmpty(mixed $val): ?string
    {
        $val = is_string($val) ? trim($val) : $val;
        return ($val === '' || $val === null) ? null : $val;
    }

    private function toNumber(mixed $val): ?float
    {
        if (is_int($val) || is_float($val)) {
            return (float) $val;
        }
        $clean = preg_replace('/[^\d.]/', '', (string) $val);
        return is_numeric($clean) ? (float) $clean : null;
    }

    private function emptyResult(): array
    {
        return [
            'raw_text' => '',
            'header'   => array_fill_keys(self::HEADER_FIELDS, null),
            'items'    => [],
            'ocr_success' => false,
        ];
    }
}