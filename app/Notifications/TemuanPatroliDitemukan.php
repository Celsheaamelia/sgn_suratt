<?php
// App\Notifications\TemuanPatroliDitemukan.php

namespace App\Notifications;

use App\Models\PatrolScan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class TemuanPatroliDitemukan extends Notification
{
    use Queueable;

    public function __construct(public PatrolScan $scan)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $checkpoint = $this->scan->checkpoint;
        $petugas    = $this->scan->session->user;

        return [
            'scan_id'      => $this->scan->id,
            'session_id'   => $this->scan->patrol_session_id,
            'status'       => $this->scan->status, // temuan | bahaya
            'titik'        => $checkpoint->nama_titik,
            'petugas'      => $petugas->username ?? '-',
            'catatan'      => $this->scan->catatan,
            'waktu'        => $this->scan->scanned_at?->format('d M Y H:i'),
            'detail_url'   => route('patroli.monitoring.show', $this->scan->patrol_session_id),
            'pesan'        => $this->scan->status === 'bahaya'
                ? "⚠️ Potensi bahaya di {$checkpoint->nama_titik} oleh {$petugas->username}"
                : "Temuan baru di {$checkpoint->nama_titik} oleh {$petugas->username}",
        ];
    }
}
