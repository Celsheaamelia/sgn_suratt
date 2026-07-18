<?php

namespace App\Http\Controllers;

use App\Models\RiwayatSurat;
use App\Models\KeepNomorSurat;

trait NomorUrut
{
    protected function usedNumbersForDate(string $tanggal): array
    {
        $riwayatNumbers = RiwayatSurat::whereDate('tanggal', $tanggal)
            ->pluck('nomor_surat')
            ->map(function ($nomorSurat) {
                $parts = explode('.', $nomorSurat);
                return (int) end($parts);
            })
            ->all();

        $keepNumbers = KeepNomorSurat::whereDate('tanggal', $tanggal)
            ->pluck('nomor')
            ->map(fn ($n) => (int) $n)
            ->all();

        return array_values(array_unique(array_merge($riwayatNumbers, $keepNumbers)));
    }

    protected function nextAvailableSequence(string $tanggal): int
    {
        $used = $this->usedNumbersForDate($tanggal);

        return $used ? max($used) + 1 : 1;
    }
}
