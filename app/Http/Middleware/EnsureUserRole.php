<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Batasi route berdasarkan role user yang login.
 *
 * Pemakaian di routes: ->middleware('role:admin') atau ->middleware('role:admin,satpam')
 *
 * PENTING: daftarkan alias middleware ini di bootstrap/app.php (Laravel 11) supaya bisa
 * dipanggil pakai nama 'role'. Lihat catatan instalasi yang saya kirim terpisah.
 */
class EnsureUserRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
