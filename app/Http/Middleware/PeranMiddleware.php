<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PeranMiddleware
{
    /**
     * Pakai: peran:admin atau peran:admin,penulis. Pengguna nonaktif selalu ditolak.
     */
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->is_active) {
            auth()->logout();

            return redirect()->route('admin.masuk');
        }

        abort_unless(in_array($user->peran, $peran, true), 403);

        return $next($request);
    }
}
