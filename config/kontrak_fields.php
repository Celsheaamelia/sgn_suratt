<?php

return [
    // key => label yang ditampilkan di dropdown halaman mapping.
    // Key HARUS persis sama dengan yang dipakai di
    // KontrakController::generateDocument() - jangan diubah sembarangan,
    // kalau beda dikit maka placeholder tidak akan terisi saat generate.
    'options' => [
        'NOMOR_KONTRAK'             => 'Nomor Kontrak',
        'JENIS_KONTRAK'             => 'Jenis Kontrak',
        'TANGGAL_KONTRAK'           => 'Tanggal Kontrak (lengkap, cth: 3 Agustus 2026)',
        'PADA_HARI_INI'             => 'Nama Hari (cth: Senin)',
        'TANGGAL_KONTRAK_TERBILANG' => 'Tanggal Kontrak (terbilang, cth: tiga)',
        'BULAN_KONTRAK'             => 'Nama Bulan (cth: Agustus)',
        'TANGGAL_KONTRAK_SINGKAT'   => 'Tanggal Kontrak (format dd-mm-yyyy)',

        'NAMA_PENANDATANGAN'        => 'Nama Penandatangan (Pihak Kesatu)',
        'JABATAN_PENANDATANGAN'     => 'Jabatan Penandatangan',
        'NO_SK_PENANDATANGAN'       => 'No. SK / Surat Tugas Penandatangan',
        'TANGGAL_SK_PENANDATANGAN'  => 'Tanggal SK / Surat Tugas',

        'NAMA_KARYAWAN'             => 'Nama Karyawan',
        'NIK_KARYAWAN'              => 'NIK Karyawan',
        'NO_KTP_KARYAWAN'           => 'No. KTP Karyawan',
        'TEMPAT_TANGGAL_LAHIR'      => 'Tempat/Tanggal Lahir',
        'JENIS_KELAMIN'             => 'Jenis Kelamin',
        'AGAMA'                     => 'Agama',
        'STATUS_PERKAWINAN'         => 'Status Perkawinan',
        'ALAMAT_KARYAWAN'           => 'Alamat Karyawan',
        'JABATAN_KARYAWAN'          => 'Jabatan (di kontrak ini)',
        'DEPARTEMEN'                => 'Bagian / Departemen',
        'RINCIAN_PEKERJAAN_1'       => 'Rincian Pekerjaan (a)',
        'RINCIAN_PEKERJAAN_2'       => 'Rincian Pekerjaan (b)',
        'RINCIAN_PEKERJAAN_3'       => 'Rincian Pekerjaan (c)',

        'TANGGAL_MULAI'             => 'Tanggal Mulai Berlaku',
        'TANGGAL_SELESAI'           => 'Tanggal Selesai',

        'GAJI_POKOK'                => 'Gaji Pokok (angka)',
        'GAJI_POKOK_TERBILANG'      => 'Gaji Pokok (terbilang)',
        'UPAH_LEMBUR_SEJAM'         => 'Upah Lembur per Jam',

        'CATATAN'                   => 'Catatan',
    ],

    // Dipakai TemplateFieldDetector buat nebak field dari teks LABEL yang
    // nempel di depan titik-titik (huruf kecil semua, dicek pakai
    // str_contains). Ini cuma SARAN awal - admin tetap konfirmasi/koreksi
    // lewat dropdown di halaman mapping, jadi kalau tebakannya salah/kurang
    // lengkap tidak apa-apa, tambah kata kunci di sini kapan saja.
    'keywords' => [
        'NOMOR_KONTRAK'             => ['nomor'],
        'PADA_HARI_INI'             => ['pada hari ini', 'hari ini'],
        'BULAN_KONTRAK'             => ['bulan'],
        'TANGGAL_KONTRAK_TERBILANG' => ['tanggal'],

        'NAMA_KARYAWAN'             => ['nama/identitas', 'nama lengkap', 'nama karyawan'],
        'TEMPAT_TANGGAL_LAHIR'      => ['tempat/tanggal lahir', 'tempat tanggal lahir'],
        'JENIS_KELAMIN'             => ['jenis kelamin'],
        'AGAMA'                     => ['agama'],
        'STATUS_PERKAWINAN'         => ['status perkawinan'],
        'ALAMAT_KARYAWAN'           => ['alamat'],
        'NO_KTP_KARYAWAN'           => ['identitas ktp', 'no. ktp', 'no ktp'],

        'JABATAN_KARYAWAN'          => ['jabatan'],
        'DEPARTEMEN'                => ['bagian/sub bagian', 'bagian', 'departemen'],

        'TANGGAL_MULAI'             => ['mulai tanggal', 'terhitung mulai'],
        'GAJI_POKOK'                => ['gaji', 'upah'],
    ],
];
