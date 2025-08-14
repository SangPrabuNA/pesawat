<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class CheckIsMaster
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->role === 'master') {
            return $next($request);
        }
        return redirect()->route('dashboard')->with('error', 'Akses ditolak. Fitur ini hanya untuk Master.');
    }
}
