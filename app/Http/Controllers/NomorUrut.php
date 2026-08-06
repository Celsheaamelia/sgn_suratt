<?php

namespace App\Http\Controllers;

use App\Models\RiwayatSurat;
use App\Models\Kontrak;

trait NomorUrut
{
    /**
     * Semua nomor urut yang udah kepake di tanggal tertentu,
     * digabung dari RiwayatSurat DAN Kontrak (KTR + PJJ),
     * karena semuanya berbagi satu rangkaian nomor.
     */
    protected function usedNumbersForDate(string $tanggal): array
    {
        $suratNumbers = RiwayatSurat::whereDate('tanggal', $tanggal)
            ->pluck('nomor_surat');

        $kontrakNumbers = Kontrak::whereDate('tanggal', $tanggal)
            ->pluck('nomor_kontrak');

        return $suratNumbers
            ->concat($kontrakNumbers)
            ->map(function ($nomor) {
                $parts = explode('.', $nomor);
                return (int) end($parts);
            })
            ->unique()
            ->values()
            ->all();
    }

    protected function nextAvailableSequence(string $tanggal): int
    {
        $used = $this->usedNumbersForDate($tanggal);

        return $used ? max($used) + 1 : 1;
    }

    /**
     * Peta nomor urut -> status, gabungan dari RiwayatSurat dan Kontrak.
     */
   protected function numberStatusMapForDate(string $tanggal): array
    {
        $surat = RiwayatSurat::whereDate('tanggal', $tanggal)
            ->get(['nomor_surat as nomor', 'status']);

        $kontrak = Kontrak::whereDate('tanggal', $tanggal)
            ->get(['nomor_kontrak as nomor', 'status']);

        return $surat->concat($kontrak)
            ->mapWithKeys(function ($row) {
                $parts = explode('.', $row->nomor);
                $seq = (int) end($parts);
                return [$seq => $row->status];
            })
            ->all();
    }

    protected function groupedUsedNumbersForDate(string $tanggal): array
    {
        $map = $this->numberStatusMapForDate($tanggal);

        $terpakai = [];
        $direservasi = [];

        foreach ($map as $nomor => $status) {
            if ($status === 'Direservasi') {
                $direservasi[] = $nomor;
            } else {
                $terpakai[] = $nomor;
            }
        }

        sort($terpakai);
        sort($direservasi);

        return [
            'terpakai'    => $terpakai,
            'direservasi' => $direservasi,
        ];
    }
}
