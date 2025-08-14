<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class CheckHasAdminAccess
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && in_array(auth()->user()->role, ['master', 'admin'])) {
            return $next($request);
        }
        return redirect()->route('dashboard')->with('error', 'Akses ditolak. Fitur ini hanya untuk Admin atau Master.');
    }
}
