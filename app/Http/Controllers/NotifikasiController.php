<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotifikasiController extends Controller
{
    /**
     * Dipanggil polling dari lonceng navbar setiap beberapa detik.
     */
    public function data()
    {
        $user = Auth::user();

        $unread = $user->unreadNotifications()->take(8)->get()->map(function ($n) {
            return [
                'id'         => $n->id,
                'pesan'      => $n->data['pesan'] ?? 'Notifikasi baru',
                'waktu'      => $n->created_at->diffForHumans(),
                'detail_url' => $n->data['detail_url'] ?? null,
            ];
        });

        return response()->json([
            'unread_count' => $user->unreadNotifications()->count(),
            'items'        => $unread,
        ]);
    }

    public function baca(string $id)
    {
        $notif = Auth::user()->notifications()->where('id', $id)->first();

        if ($notif) {
            $notif->markAsRead();
        }

        $url = $notif->data['detail_url'] ?? null;

        return $url ? redirect($url) : back();
    }

    public function bacaSemua()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return back();
    }
}
