<?php

namespace App\Services;

use ZipArchive;
use DOMDocument;
use RuntimeException;

/**
 * Baca file .docx, cari pola "titik-titik kosong" (bagian yang harus diisi
 * manual di draft Word) per paragraf, lalu sarankan field placeholder yang
 * cocok berdasarkan label di depannya. Hasilnya dipakai untuk menampilkan
 * halaman "Petakan Field" - admin tinggal konfirmasi lewat dropdown, tidak
 * perlu menulis {{...}} sendiri.
 */
class TemplateFieldDetector
{
    private const NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    // Urutan 3+ karakter titik biasa / ellipsis (…) / underscore, dengan
    // atau tanpa spasi di antaranya - ini pola "kosong yang harus diisi"
    // di draft Word. Contoh yang harus ke-match: "......", "…………….", "___".
    private const DOT_PATTERN = '/[.\x{2026}_](?:[.\x{2026}_\s]{1,}[.\x{2026}_])+|[.\x{2026}_]{3,}/u';

    /**
     * @return array<int, array{
     *     paragraph_index:int, offset:int, matched_text:string,
     *     label:string, context:string, suggested_field:?string
     * }>
     */
    public function detect(string $templatePath): array
    {
        if (!file_exists($templatePath)) {
            throw new RuntimeException("Template tidak ditemukan: {$templatePath}");
        }

        $zip = new ZipArchive();
        if ($zip->open($templatePath) !== true) {
            throw new RuntimeException("Gagal membuka file docx: {$templatePath}");
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            return [];
        }

        $dom = new DOMDocument();
        $prevErrors = libxml_use_internal_errors(true);
        $dom->loadXML($xml);
        libxml_use_internal_errors($prevErrors);

        $paragraphs = $dom->getElementsByTagNameNS(self::NS, 'p');
        $keywordMap = config('kontrak_fields.keywords', []);

        $results = [];
        $pIndex = 0;

        foreach ($paragraphs as $paragraph) {
            $text = self::mergedPlainText($paragraph);

            if (preg_match_all(self::DOT_PATTERN, $text, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[0] as [$matchedText, $byteOffset]) {
                    // PREG_OFFSET_CAPTURE mengembalikan offset byte, bukan
                    // karakter - perlu dikonversi karena teks bisa
                    // mengandung karakter multi-byte (mis. "…", "–").
                    $offset = mb_strlen(substr($text, 0, $byteOffset));

                    $before = mb_substr($text, 0, $offset);
                    $label  = $this->extractLabel($before);

                    $results[] = [
                        'paragraph_index' => $pIndex,
                        'offset'          => $offset,
                        'matched_text'    => $matchedText,
                        'label'           => $label,
                        'context'         => trim(mb_substr($text, max(0, $offset - 40), 90)),
                        'suggested_field' => $this->suggestField($label, $keywordMap),
                    ];
                }
            }

            $pIndex++;
        }

        return $results;
    }

    /**
     * Gabungkan teks <w:t> dalam satu paragraf TANPA tab/br (harus identik
     * dengan yang dipakai DocxTemplateService::applyFieldMapping() saat
     * menulis balik, supaya offset yang dihasilkan di sini tetap valid
     * dipakai untuk splice teks nanti).
     */
    public static function mergedPlainText($paragraph): string
    {
        $text = '';
        foreach ($paragraph->getElementsByTagNameNS(self::NS, 't') as $t) {
            $text .= $t->textContent;
        }

        return $text;
    }

    private function extractLabel(string $before): string
    {
        $before = rtrim($before);

        if (str_contains($before, ':')) {
            $parts = explode(':', $before);
            array_pop($parts); // buang bagian kosong setelah ':' terakhir
            $label = trim(end($parts));

            if ($label !== '') {
                return $label;
            }
        }

        // tidak ada ':' - ambil beberapa kata terakhir sebagai konteks
        $words = preg_split('/\s+/', $before) ?: [];

        return trim(implode(' ', array_slice($words, -5)));
    }

    private function suggestField(string $label, array $keywordMap): ?string
    {
        $normalized = strtolower($label);

        if ($normalized === '') {
            return null;
        }

        foreach ($keywordMap as $field => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($normalized, $keyword)) {
                    return $field;
                }
            }
        }

        return null;
    }
}
