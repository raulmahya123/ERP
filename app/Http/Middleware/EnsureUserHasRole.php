<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     *
     * Pemakaian di route:
     *   ->middleware('role:gm,manager')
     *
     * Bisa lebih dari satu role, dipisah koma.
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        // Kalau belum login → lempar ke login
        if (!$user) {
            return redirect()->route('login');
        }

        // Kalau user punya salah satu role → lanjut
        if ($user->role && in_array($user->role->key, $roles)) {
            return $next($request);
        }

        // Kalau tidak sesuai → 403 Forbidden
        abort(403, 'Unauthorized — role tidak diizinkan.');
    }
}
