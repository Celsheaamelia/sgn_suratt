<?php

namespace App\Exports;

use App\Models\RiwayatSurat;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RiwayatSuratExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected $search;
    protected $klasifikasi;
    protected $sort;
    protected $tanggalDari;
    protected $tanggalSampai;

    public function __construct($search = null, $klasifikasi = null, $sort = 'desc', $tanggalDari = null, $tanggalSampai = null)
    {
        $this->search = $search;
        $this->klasifikasi = $klasifikasi;
        $this->sort = $sort;
        $this->tanggalDari = $tanggalDari;
        $this->tanggalSampai = $tanggalSampai;
    }

    public function collection()
    {
        $query = RiwayatSurat::with(['penandatangan', 'tujuanSurat', 'klasifikasiSurat']);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('nomor_surat', 'like', '%' . $this->search . '%')
                  ->orWhere('perihal', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->klasifikasi) {
            $query->whereHas('klasifikasiSurat', function ($q) {
                $q->where('kode', $this->klasifikasi);
            });
        }

        if ($this->tanggalDari) {
            $query->whereDate('tanggal', '>=', $this->tanggalDari);
        }

        if ($this->tanggalSampai) {
            $query->whereDate('tanggal', '<=', $this->tanggalSampai);
        }

        $query->orderBy('tanggal', $this->sort === 'asc' ? 'asc' : 'desc');

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'Nomor Surat',
            'Perihal',
            'Tujuan',
            'Penandatangan',
            'Tanggal',
            'Status',
        ];
    }

    public function map($surat): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $surat->nomor_surat,
            $surat->perihal,
            $surat->tujuanSurat->nama_tujuan ?? '-',
            $surat->penandatangan->jabatan ?? '-',
            $surat->tanggal,
            $surat->status ?? 'Belum Terupload',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}