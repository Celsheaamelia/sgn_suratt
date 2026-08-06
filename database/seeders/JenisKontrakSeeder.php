<?php

namespace Database\Seeders;

use App\Models\JenisKontrak;
use Illuminate\Database\Seeder;

class JenisKontrakSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            [
                'kode'          => 'PKWT-DMG',
                'kode_nomor'    => 'PJJ', // rangkaian nomor terpisah dari KTR
                'nama_jenis'    => 'PKWT Dalam Masa Giling (DMG)',
                'nama_singkat'  => 'PKWT DMG',
                'masa_giling'   => true,  // tanggal_selesai = akhir masa giling, bukan tanggal tetap
                'masa_berlaku_bulan' => null,
                'gaji_pokok_default' => 2578320,
                'template_file' => 'templates/kontrak/template_pkwt_dmg_pjj.docx',
                'deskripsi'     => 'Kontrak untuk karyawan yang bekerja selama masa giling berlangsung.',
            ],
            [
                'kode'          => 'PKWT-LMG',
                'kode_nomor'    => 'KTR',
                'nama_jenis'    => 'PKWT Luar/Dalam Masa Giling (LMG-DMG, 12 Bulan)',
                'nama_singkat'  => 'PKWT DMG-LMG',
                'masa_giling'   => false,
                'masa_berlaku_bulan' => 12,
                'gaji_pokok_default' => 2578320,
                'template_file' => 'templates/kontrak/template_pkwt_lmg_ktr.docx',
                'deskripsi'     => 'Kontrak reguler dengan periode tetap (biasanya awal tahun s/d akhir Desember).',
            ],
        ];

        foreach ($data as $row) {
            JenisKontrak::updateOrCreate(['kode' => $row['kode']], $row);
        }
    }
}