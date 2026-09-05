<?php

namespace App\Console\Commands;

use App\Models\PatrolScan;
use App\Models\User;
use App\Notifications\TemuanBelumDitangani;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class EskalasiTemuanPatroli extends Command
{
    protected $signature = 'patroli:eskalasi-temuan';

    protected $description = 'Kirim notifikasi eskalasi untuk temuan/potensi bahaya yang belum ditindaklanjuti melewati ambang waktu (SOP: "koordinasi jika eskalasi diperlukan")';

    /**
     * Ambang waktu (jam sejak scan) => level eskalasi.
     * Level 1 = pengingat awal, 2 = mendesak, 3 = kritis (dianggap perlu koordinasi lebih tinggi).
     * Silakan sesuaikan angka jam ini dengan SLA penanganan temuan yang berlaku di lapangan.
     */
    private const AMBANG_JAM = [
        1 => 1,
        2 => 3,
        3 => 6,
    ];

    public function handle(): int
    {
        $temuanBelumDitangani = PatrolScan::with(['checkpoint', 'session.user'])
            ->temuan()
            ->belumDitangani()
            ->get();

        if ($temuanBelumDitangani->isEmpty()) {
            $this->info('Tidak ada temuan yang perlu dieskalasi.');
            return self::SUCCESS;
        }

        $penerima = User::whereIn('role', [User::ROLE_ADMIN, User::ROLE_SUPERVISOR])->get();
        $jumlahDikirim = 0;

        foreach ($temuanBelumDitangani as $scan) {
            if (! $scan->scanned_at) {
                continue;
            }

            $jamBerlalu = (int) $scan->scanned_at->diffInHours(now());

            // Tentukan level eskalasi tertinggi yang sudah terlampaui waktunya.
            $levelSeharusnya = 0;
            foreach (self::AMBANG_JAM as $level => $ambangJam) {
                if ($jamBerlalu >= $ambangJam) {
                    $levelSeharusnya = $level;
                }
            }

            // Belum melewati ambang level manapun, atau levelnya sudah pernah dikirim → skip.
            if ($levelSeharusnya === 0 || $levelSeharusnya <= $scan->eskalasi_level) {
                continue;
            }

            Notification::send($penerima, new TemuanBelumDitangani($scan, $levelSeharusnya, $jamBerlalu));

            $scan->update([
                'eskalasi_level'       => $levelSeharusnya,
                'eskalasi_terakhir_at' => now(),
            ]);

            $jumlahDikirim++;

            $this->line("Eskalasi level {$levelSeharusnya} dikirim untuk scan #{$scan->id} ({$scan->checkpoint->nama_titik}, {$jamBerlalu} jam berlalu).");
        }

        $this->info("Selesai. {$jumlahDikirim} notifikasi eskalasi dikirim.");

        return self::SUCCESS;
    }
}
