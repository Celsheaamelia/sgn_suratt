<?php

namespace App\Exports;

use App\Models\ArsipKasbon;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export arsip SPP ke Excel.
 *
 * Satu baris Excel = satu baris rincian akun (bukan satu baris per surat),
 * supaya semua item ikut terbawa ke spreadsheet — data header surat
 * diulang di tiap barisnya seperti laporan akuntansi pada umumnya.
 *
 * Filter (q, tanggal_dari, tanggal_sampai) sama persis dengan yang dipakai
 * di ArsipKasbonController::index(), supaya hasil export selalu konsisten
 * dengan apa yang lagi ditampilkan/dicari di layar.
 */
class ArsipKasbonExport implements FromQuery, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = ArsipKasbon::with('items')->latest();

        if (!empty($this->filters['q'])) {
            $keyword = trim($this->filters['q']);

            $query->where(function ($sub) use ($keyword) {
                $sub->where('document_no', 'like', "%{$keyword}%")
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

        if (!empty($this->filters['tanggal_dari'])) {
            $query->whereDate('tanggal_transaksi', '>=', $this->filters['tanggal_dari']);
        }

        if (!empty($this->filters['tanggal_sampai'])) {
            $query->whereDate('tanggal_transaksi', '<=', $this->filters['tanggal_sampai']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Tanggal Transaksi',
            'Document No',
            'Park Oleh',
            'Nama Vendor',
            'Kode Vendor',
            'Cek/Giro/Trx',
            'Deskripsi Cost Object',
            'Jumlah Total Surat',
            'Terbilang',
            'No Akun',
            'PK',
            'Cost Object (Akun)',
            'Item Text',
            'Jumlah (Akun)',
        ];
    }

    /**
     * Laravel Excel mendukung 1 model -> banyak baris kalau map()
     * mengembalikan array of arrays. Dipakai di sini supaya tiap
     * rincian akun jadi barisnya sendiri.
     */
    public function map($kasbon): array
    {
        $baseRow = [
            optional($kasbon->tanggal_transaksi)->format('d-m-Y'),
            $kasbon->document_no,
            $kasbon->park_oleh,
            $kasbon->nama_vendor,
            $kasbon->kode_vendor,
            $kasbon->cek_giro_trx,
            $kasbon->deskripsi_cost_object,
            $kasbon->jumlah_total,
            $kasbon->terbilang,
        ];

        if ($kasbon->items->isEmpty()) {
            return [array_merge($baseRow, ['-', '-', '-', '-', 0])];
        }

        return $kasbon->items->map(function ($item) use ($baseRow) {
            return array_merge($baseRow, [
                $item->no_akun,
                $item->pk,
                $item->cost_object,
                $item->item_text,
                $item->jumlah_rupiah,
            ]);
        })->all();
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
