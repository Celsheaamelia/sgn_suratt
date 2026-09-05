<?php
// App\Notifications\TemuanBelumDitangani.php
//
// Notifikasi eskalasi otomatis. SOP bagian 3 (Penanganan Temuan) menyebut
// "koordinasi jika eskalasi diperlukan" tapi sebelumnya tidak ada mekanisme apa pun
// di sistem untuk itu — semua eskalasi manual. Notifikasi ini dikirim otomatis oleh
// command patroli:eskalasi-temuan ketika sebuah temuan/bahaya belum ditandai
// "ditangani" setelah melewati ambang waktu tertentu, dengan level makin mendesak.

namespace App\Notifications;

use App\Models\PatrolScan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class TemuanBelumDitangani extends Notification
{
    use Queueable;

    /**
     * @param PatrolScan $scan
     * @param int $level 1 = pengingat, 2 = mendesak, 3 = kritis (lihat EskalasiTemuanPatroli::LEVEL)
     * @param int $jamBerlalu Jumlah jam sejak temuan pertama kali dilaporkan
     */
    public function __construct(
        public PatrolScan $scan,
        public int $level,
        public int $jamBerlalu,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $checkpoint = $this->scan->checkpoint;
        $petugas    = $this->scan->session->user;

        $labelLevel = match ($this->level) {
            3       => '🔴 KRITIS',
            2       => '🟠 MENDESAK',
            default => '🟡 PENGINGAT',
        };

        $jenis = $this->scan->status === 'bahaya' ? 'Potensi bahaya' : 'Temuan';

        return [
            'scan_id'      => $this->scan->id,
            'session_id'   => $this->scan->patrol_session_id,
            'status'       => $this->scan->status,
            'level'        => $this->level,
            'titik'        => $checkpoint->nama_titik,
            'petugas'      => $petugas->username ?? '-',
            'jam_berlalu'  => $this->jamBerlalu,
            'detail_url'   => route('patroli.monitoring.show', $this->scan->patrol_session_id),
            'pesan'        => "{$labelLevel} {$jenis} di {$checkpoint->nama_titik} sudah {$this->jamBerlalu} jam belum ditindaklanjuti (dilaporkan oleh {$petugas->username}).",
        ];
    }
}
