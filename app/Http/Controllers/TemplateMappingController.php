<?php

namespace App\Http\Controllers;

use App\Models\Template;
use App\Services\DocxTemplateService;
use App\Services\TemplateFieldDetector;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TemplateMappingController extends Controller
{
    /**
     * Tampilkan halaman "Petakan Field": scan template, tampilkan tiap
     * titik-titik yang ketemu beserta saran field-nya, admin konfirmasi
     * lewat dropdown.
     */
    public function show(Template $template, TemplateFieldDetector $detector)
    {
        $templatePath = Storage::path($template->file_path);

        $detected = $detector->detect($templatePath);

        return view('template-kontrak.mapping', [
            'template'     => $template,
            'detected'     => $detected,
            'fieldOptions' => config('kontrak_fields.options'),
        ]);
    }

    /**
     * Terima konfirmasi admin dari dropdown, tulis {{FIELD}} ke file docx
     * menggantikan titik-titik yang sudah dikonfirmasi.
     */
    public function apply(Request $request, Template $template, DocxTemplateService $docxService)
    {
        $request->validate([
            'fields' => 'required|array',
        ]);

        // Bentuk input dari form:
        // fields[<paragraph_index>][<urutan>][offset]        = int
        // fields[<paragraph_index>][<urutan>][matched_text]  = string
        // fields[<paragraph_index>][<urutan>][field]         = string ('' kalau "Lewati")
        $mapping = [];
        $confirmedCount = 0;

        foreach ($request->input('fields') as $pIndex => $items) {
            foreach ($items as $item) {
                if (empty($item['field'])) {
                    continue; // admin pilih "Lewati / bukan field data"
                }

                $mapping[(int) $pIndex][] = [
                    'offset'       => (int) $item['offset'],
                    'matched_text' => $item['matched_text'],
                    'field'        => $item['field'],
                ];
                $confirmedCount++;
            }
        }

        if ($confirmedCount === 0) {
            return back()->with('error', 'Belum ada field yang dikonfirmasi. Pilih minimal satu field lalu simpan.');
        }

        $templatePath = Storage::path($template->file_path);

        try {
            $docxService->applyFieldMapping($templatePath, $templatePath, $mapping);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'Gagal memetakan field: ' . $e->getMessage());
        }

        return redirect()->route('kontrak-template.index')
            ->with('success', "{$confirmedCount} field berhasil dipetakan ke template \"{$template->nama_template}\".");
    }
}
