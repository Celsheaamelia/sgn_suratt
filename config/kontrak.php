<?php

return [
    /**
     * Prefix konstan nomor surat kontrak, sebelum "/{kode_nomor}/{Ymd}.{urut}".
     * Contoh hasil akhir: SG26-PERSE-KTR/20260202.009
     *
     * Ganti nilai ini tiap tahun (atau pindahkan ke database kalau ternyata
     * butuh beda-beda per unit kerja / per tahun berjalan tanpa deploy ulang).
     */
    'nomor_prefix' => env('KONTRAK_NOMOR_PREFIX', 'SG26-PERSE'),
];
