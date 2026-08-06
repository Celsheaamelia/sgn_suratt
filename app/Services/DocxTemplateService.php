<?php

namespace App\Services;

use ZipArchive;
use DOMDocument;
use RuntimeException;

class DocxTemplateService
{
    private const NS = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

    /**
     * @param  string $templatePath  Path absolut ke file .docx template
     * @param  array<string,string> $data  key => value, key TANPA kurung kurawal
     *                                     contoh: ['NAMA_KARYAWAN' => 'Budi', ...]
     * @param  string $outputPath    Path absolut tujuan file hasil generate
     * @return string                Path file yang berhasil dibuat
     */
    public function generate(string $templatePath, array $data, string $outputPath): string
    {
        if (!file_exists($templatePath)) {
            throw new RuntimeException("Template tidak ditemukan: {$templatePath}");
        }

        if (!is_dir(dirname($outputPath))) {
            mkdir(dirname($outputPath), 0775, true);
        }

        if (!copy($templatePath, $outputPath)) {
            throw new RuntimeException("Gagal menyalin template ke: {$outputPath}");
        }

        $zip = new ZipArchive();
        if ($zip->open($outputPath) !== true) {
            throw new RuntimeException("Gagal membuka file docx: {$outputPath}");
        }

        // Bagian dalam docx yang mungkin memuat teks/placeholder
        $targets = ['word/document.xml', 'word/header1.xml', 'word/header2.xml',
                    'word/header3.xml', 'word/footer1.xml', 'word/footer2.xml', 'word/footer3.xml'];

        foreach ($targets as $target) {
            $xml = $zip->getFromName($target);
            if ($xml === false) {
                continue;
            }

            $xml = $this->replaceInXml($xml, $data);
            $zip->addFromString($target, $xml);
        }

        $zip->close();

        return $outputPath;
    }

    /**
     * Tulis placeholder {{FIELD}} ke posisi yang sudah dikonfirmasi admin
     * lewat halaman "Petakan Field" (lihat TemplateFieldDetector). Dipakai
     * sekali saja pas nyiapin template baru, BUKAN dipanggil pas generate
     * kontrak per karyawan (itu tetap pakai generate() di atas).
     *
     * @param  string $templatePath  Path absolut file .docx sumber (draft admin)
     * @param  string $outputPath    Path absolut tujuan (boleh sama dengan
     *                                $templatePath untuk menimpa file yang sama)
     * @param  array<int, array<int, array{offset:int, matched_text:string, field:string}>> $mapping
     *                                key luar = index paragraf (dari TemplateFieldDetector),
     *                                tiap paragraf berisi daftar titik-titik yang
     *                                dikonfirmasi + field yang dipilih admin.
     * @return string  Path file hasil
     */
    public function applyFieldMapping(string $templatePath, string $outputPath, array $mapping): string
    {
        if (!file_exists($templatePath)) {
            throw new RuntimeException("Template tidak ditemukan: {$templatePath}");
        }

        if (empty($mapping)) {
            throw new RuntimeException('Tidak ada field yang dikonfirmasi untuk dipetakan.');
        }

        // Kalau tujuan sama dengan sumber (menimpa file yang sama), proses
        // lewat file sementara dulu - copy() dari file ke dirinya sendiri
        // sambil ZipArchive dibuka bisa merusak filenya.
        $isSameFile = realpath($templatePath) !== false
            && realpath($templatePath) === realpath($outputPath);
        $workingPath = $isSameFile ? $outputPath . '.tmp' : $outputPath;

        if (!is_dir(dirname($workingPath))) {
            mkdir(dirname($workingPath), 0775, true);
        }

        if (!copy($templatePath, $workingPath)) {
            throw new RuntimeException("Gagal menyalin template ke: {$workingPath}");
        }

        $zip = new ZipArchive();
        if ($zip->open($workingPath) !== true) {
            throw new RuntimeException("Gagal membuka file docx: {$workingPath}");
        }

        $xml = $zip->getFromName('word/document.xml');
        if ($xml === false) {
            $zip->close();
            throw new RuntimeException('word/document.xml tidak ditemukan di dalam file.');
        }

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = true;
        $prevErrors = libxml_use_internal_errors(true);
        $loaded = $dom->loadXML($xml);
        libxml_use_internal_errors($prevErrors);

        if (!$loaded) {
            $zip->close();
            throw new RuntimeException('Gagal membaca XML dokumen (file mungkin korup).');
        }

        $paragraphs = iterator_to_array($dom->getElementsByTagNameNS(self::NS, 'p'));

        foreach ($mapping as $pIndex => $replacements) {
            if (!isset($paragraphs[$pIndex]) || empty($replacements)) {
                continue;
            }

            $paragraph = $paragraphs[$pIndex];
            $text = TemplateFieldDetector::mergedPlainText($paragraph);

            // Urutkan dari offset TERBESAR dulu, supaya splice dari
            // belakang - offset yang lebih awal tidak ikut bergeser waktu
            // panjang teks berubah karena penggantian.
            usort($replacements, fn ($a, $b) => $b['offset'] <=> $a['offset']);

            foreach ($replacements as $r) {
                $start  = $r['offset'];
                $length = mb_strlen($r['matched_text']);

                $text = mb_substr($text, 0, $start)
                    . '{{' . $r['field'] . '}}'
                    . mb_substr($text, $start + $length);
            }

            $this->rebuildParagraphFromText($paragraph, $text);
        }

        $zip->addFromString('word/document.xml', $dom->saveXML());
        $zip->close();

        if ($isSameFile) {
            if (!rename($workingPath, $outputPath)) {
                throw new RuntimeException("Gagal menyimpan hasil ke: {$outputPath}");
            }
        }

        return $outputPath;
    }

    /**
     * @param  string $docxPath  Path absolut ke file .docx sumber
     * @param  string|null $outputDir  Folder tujuan PDF (default: folder yang sama dengan docx)
     * @return string  Path absolut file .pdf hasil konversi
     */
    public function convertToPdf(string $docxPath, ?string $outputDir = null): string
    {
        if (!file_exists($docxPath)) {
            throw new RuntimeException("File docx tidak ditemukan: {$docxPath}");
        }

        $outputDir = $outputDir ?: dirname($docxPath);
        $pdfPath = rtrim($outputDir, '/') . '/' . pathinfo($docxPath, PATHINFO_FILENAME) . '.pdf';

        // Kalau pdf sudah ada dan lebih baru dari docx-nya, tidak perlu convert ulang.
        if (file_exists($pdfPath) && filemtime($pdfPath) >= filemtime($docxPath)) {
            return $pdfPath;
        }

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0775, true);
        }

        $userProfileDir = sys_get_temp_dir() . '/soffice-profile-' . uniqid();

        $cmd = sprintf(
            'soffice --headless --norestore -env:UserInstallation=file://%s --convert-to pdf --outdir %s %s 2>&1',
            escapeshellarg($userProfileDir),
            escapeshellarg($outputDir),
            escapeshellarg($docxPath)
        );

        exec($cmd, $output, $exitCode);

        // Bersihkan profile sementara, tidak dibutuhkan lagi setelah convert selesai.
        exec('rm -rf ' . escapeshellarg($userProfileDir));

        if ($exitCode !== 0 || !file_exists($pdfPath)) {
            throw new RuntimeException(
                'Gagal convert docx ke PDF. Pastikan LibreOffice (soffice) terinstall di server. Output: '
                . implode("\n", $output)
            );
        }

        return $pdfPath;
    }

    /**
     * Ambil semua placeholder {{...}} yang ada di dalam template, dipakai
     * untuk validasi "field apa saja yang tersedia di template ini".
     *
     * @return string[]
     */
    public function extractPlaceholders(string $templatePath): array
    {
        if (!file_exists($templatePath)) {
            return [];
        }

        $zip = new ZipArchive();
        if ($zip->open($templatePath) !== true) {
            return [];
        }

        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if ($xml === false) {
            return [];
        }

        $plainText = $this->flattenParagraphText($xml);

        preg_match_all('/\{\{\s*([A-Z0-9_]+)\s*\}\}/', $plainText, $matches);

        return array_values(array_unique($matches[1] ?? []));
    }

    /**
     * Gabungkan run per paragraf lalu replace placeholder pada XML docx.
     */
    private function replaceInXml(string $xml, array $data): string
    {
        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = true;
        $dom->formatOutput = false;

        $prevErrors = libxml_use_internal_errors(true);
        $loaded = $dom->loadXML($xml);
        libxml_use_internal_errors($prevErrors);

        if (!$loaded) {
            // Kalau gagal parse XML, biarkan file apa adanya (jangan sampai corrupt)
            return $xml;
        }

        $paragraphs = $dom->getElementsByTagNameNS(self::NS, 'p');

        // Kumpulkan dulu ke array biasa - paragraphs akan dimodifikasi
        // (run dihapus & ditambah lagi) selagi di-loop, NodeList live query
        // bisa kacau kalau di-iterate langsung sambil diubah.
        foreach (iterator_to_array($paragraphs) as $paragraph) {
            $this->mergeAndReplaceInParagraph($paragraph, $data);
        }

        return $dom->saveXML();
    }

    private function mergeAndReplaceInParagraph($paragraph, array $data): void
    {
        $runs = $paragraph->getElementsByTagNameNS(self::NS, 'r');

        if ($runs->length === 0) {
            return;
        }

        // Gabungkan teks paragraf, TERMASUK tab (<w:tab/> -> "\t") dan baris
        // baru (<w:br/> -> "\n"), supaya alignment tidak hilang waktu digabung.
        $fullText = $this->mergedTextWithTabsAndBreaks($paragraph);

        if (strpos($fullText, '{{') === false) {
            return; // tidak ada placeholder di paragraf ini, biarkan apa adanya
        }

        $replaced = preg_replace_callback('/\{\{\s*([A-Z0-9_]+)\s*\}\}/', function ($m) use ($data) {
            $key = $m[1];
            return array_key_exists($key, $data) ? (string) $data[$key] : $m[0];
        }, $fullText);

        $this->rebuildParagraphFromText($paragraph, $replaced);
    }

    private function mergedTextWithTabsAndBreaks($paragraph): string
    {
        $fullText = '';
        foreach ($paragraph->getElementsByTagNameNS(self::NS, 'r') as $run) {
            foreach ($run->childNodes as $child) {
                if ($child->nodeType !== XML_ELEMENT_NODE) {
                    continue;
                }
                if ($child->localName === 't') {
                    $fullText .= $child->textContent;
                } elseif ($child->localName === 'tab') {
                    $fullText .= "\t";
                } elseif ($child->localName === 'br' || $child->localName === 'cr') {
                    $fullText .= "\n";
                }
            }
        }

        return $fullText;
    }

    /**
     * Hapus semua run yang ada di paragraf, lalu bangun ulang dari teks
     * final yang sudah diproses (baik dari replace placeholder biasa maupun
     * dari applyFieldMapping). Format (bold, dsb) diambil dari run pertama
     * yang punya rPr supaya konsisten; highlight (kuning, dsb) SENGAJA
     * dibuang - itu cuma penanda "isi di sini" di draft, bukan bagian dari
     * dokumen final.
     */
    private function rebuildParagraphFromText($paragraph, string $finalText): void
    {
        $runList = iterator_to_array($paragraph->getElementsByTagNameNS(self::NS, 'r'));

        if ($runList === []) {
            return;
        }

        $templateRPr = null;
        foreach ($runList as $run) {
            foreach ($run->childNodes as $child) {
                if ($child->nodeType === XML_ELEMENT_NODE && $child->localName === 'rPr') {
                    $templateRPr = $child->cloneNode(true);
                    break 2;
                }
            }
        }
        if ($templateRPr !== null) {
            foreach (iterator_to_array($templateRPr->getElementsByTagNameNS(self::NS, 'highlight')) as $hl) {
                $hl->parentNode->removeChild($hl);
            }
        }

        $dom = $paragraph->ownerDocument;

        // Hapus semua run lama di paragraf ini
        foreach ($runList as $run) {
            $run->parentNode->removeChild($run);
        }

        // Bangun ulang: pecah per baris ("\n") lalu per kolom ("\t"), selingi
        // run teks baru dengan elemen <w:tab/> / <w:br/> ASLI Word.
        $lines = explode("\n", $finalText);

        foreach ($lines as $lineIndex => $line) {
            if ($lineIndex > 0) {
                $brRun = $dom->createElementNS(self::NS, 'w:r');
                if ($templateRPr) {
                    $brRun->appendChild($templateRPr->cloneNode(true));
                }
                $brRun->appendChild($dom->createElementNS(self::NS, 'w:br'));
                $paragraph->appendChild($brRun);
            }

            $parts = explode("\t", $line);
            $lastIndex = count($parts) - 1;

            foreach ($parts as $partIndex => $part) {
                if ($part !== '') {
                    $run = $dom->createElementNS(self::NS, 'w:r');
                    if ($templateRPr) {
                        $run->appendChild($templateRPr->cloneNode(true));
                    }
                    $t = $dom->createElementNS(self::NS, 'w:t');
                    $t->appendChild($dom->createTextNode($part));
                    $t->setAttribute('xml:space', 'preserve');
                    $run->appendChild($t);
                    $paragraph->appendChild($run);
                }

                if ($partIndex < $lastIndex) {
                    $tabRun = $dom->createElementNS(self::NS, 'w:r');
                    if ($templateRPr) {
                        $tabRun->appendChild($templateRPr->cloneNode(true));
                    }
                    $tabRun->appendChild($dom->createElementNS(self::NS, 'w:tab'));
                    $paragraph->appendChild($tabRun);
                }
            }
        }
    }

    private function flattenParagraphText(string $xml): string
    {
        $dom = new DOMDocument();
        $prevErrors = libxml_use_internal_errors(true);
        $loaded = $dom->loadXML($xml);
        libxml_use_internal_errors($prevErrors);

        if (!$loaded) {
            return '';
        }

        $paragraphs = $dom->getElementsByTagNameNS(self::NS, 'p');

        $out = [];
        foreach ($paragraphs as $paragraph) {
            $textNodes = $paragraph->getElementsByTagNameNS(self::NS, 't');
            $line = '';
            foreach ($textNodes as $node) {
                $line .= $node->textContent;
            }
            $out[] = $line;
        }

        return implode("\n", $out);
    }
}
