<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RedirectIfUnassigned
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Jika pengguna tidak terautentikasi atau adalah 'master', lanjutkan saja.
        if (!Auth::check() || $user->role === 'master') {
            return $next($request);
        }

        // Cek apakah pengguna adalah admin/user dan belum punya airport_id
        $isUnassigned = in_array($user->role, ['admin', 'user']) && is_null($user->airport_id);

        // Jika belum ditugaskan dan belum berada di halaman 'pending'
        if ($isUnassigned && !$request->routeIs('pending.assignment')) {
            return redirect()->route('pending.assignment');
        }

        // Jika sudah ditugaskan tapi mencoba mengakses halaman 'pending'
        if (!$isUnassigned && $request->routeIs('pending.assignment')) {
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}
