<?php

namespace App\Http\Controllers;

use App\Models\Kontrak;

trait NomorKontrakUrut
{
    /**
     * Nomor urut (segmen paling belakang setelah tanda titik) yang sudah
     * terpakai untuk tanggal + kode_nomor tertentu.
     *
     * PENTING: nomor kontrak formatnya = {PREFIX}-{KODE_NOMOR}/{Ymd}.{urut}
     * contoh SG26-PERSE-KTR/20260202.009 atau SG26-PERSE-PJJ/20260508.001
     * KTR (non masa giling) dan PJJ (masa giling) adalah 2 rangkaian nomor
     * yang terpisah, jadi urutnya harus dihitung terpisah juga per kode_nomor,
     * bukan digabung semua kontrak pada tanggal tersebut.
     */
    protected function usedContractNumbersForDate(string $tanggal): array
    {
        return Kontrak::whereDate('tanggal', $tanggal)
            ->pluck('nomor_kontrak')
            ->map(function ($nomor) {
                $parts = explode('.', $nomor);
                return (int) end($parts);
            })
            ->unique()
            ->values()
            ->all();
    }

    protected function nextAvailableContractSequence(string $tanggal): int
    {
        $used = $this->usedContractNumbersForDate($tanggal);

        return $used ? max($used) + 1 : 1;
    }

    /**
     * Peta nomor urut -> status untuk tanggal + kode_nomor tertentu, dipakai
     * membedakan nomor yang sudah jadi kontrak definitif vs yang masih
     * direservasi.
     */
    protected function contractNumberStatusMapForDate(string $tanggal): array
    {
        return Kontrak::whereDate('tanggal', $tanggal)
            ->get(['nomor_kontrak', 'status'])
            ->mapWithKeys(function ($row) {
                $parts = explode('.', $row->nomor_kontrak);
                $seq = (int) end($parts);
                return [$seq => $row->status];
            })
            ->all();
    }

    protected function groupedUsedContractNumbersForDate(string $tanggal, string $kodeNomor): array
    {
        $map = $this->contractNumberStatusMapForDate($tanggal);

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

    /**
     * Susun nomor kontrak lengkap: {PREFIX}-{KODE_NOMOR}/{Ymd}.{urut(3 digit)}
     */
    protected function buildNomorKontrak(string $kodeNomor, string $tanggal, int $urut): string
    {
        $prefix = config('kontrak.nomor_prefix', 'SG26-PERSE');
        $urutPadded = str_pad((string) $urut, 3, '0', STR_PAD_LEFT);

        return $prefix . '-' . $kodeNomor . '/' . date('Ymd', strtotime($tanggal)) . '.' . $urutPadded;
    }
}
