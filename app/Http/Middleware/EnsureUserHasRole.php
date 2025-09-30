<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserHasRole
{
    /**
     * Pakai di route: ->middleware('role:gm,manager')
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Belum login → arahkan ke login + simpan intended URL
        if (!Auth::check()) {
            return redirect()->guest(route('login'));
        }

        $user = Auth::user();

        // Normalisasi allowed roles: trim + lowercase
        $allowed = collect($roles)
            ->flatMap(fn ($r) => explode(',', $r))   // jaga-jaga kalau ada "role:gm,manager" dikirim jadi 1 arg
            ->map(fn ($r) => strtolower(trim($r)))
            ->filter()
            ->unique()
            ->values();

        // Kumpulkan role user dalam bentuk array lowercase
        $userRoles = collect();

        // 1) Kalau ada field string langsung: $user->role = 'gm'
        if (isset($user->role) && is_string($user->role)) {
            $userRoles->push(strtolower($user->role));
        }

        // 2) Kalau relasi tunggal: $user->role->key|slug|name
        if (isset($user->role) && is_object($user->role)) {
            foreach (['key', 'slug', 'name'] as $col) {
                if (!empty($user->role->{$col})) {
                    $userRoles->push(strtolower($user->role->{$col}));
                    break;
                }
            }
        }

        // 3) Kalau many-to-many: $user->roles()
        if (method_exists($user, 'roles')) {
            // coba urutan kolom umum
            foreach (['key', 'slug', 'name'] as $col) {
                try {
                    $vals = $user->roles()->pluck($col)->filter()->map(fn ($v) => strtolower($v));
                    if ($vals->isNotEmpty()) {
                        $userRoles = $userRoles->merge($vals);
                        break;
                    }
                } catch (\Throwable $e) {
                    // kolomnya nggak ada—lanjut coba kolom berikut
                }
            }
        }

        $userRoles = $userRoles->unique()->values();

        // Jika user memiliki salah satu role yang diizinkan → lanjut
        if ($allowed->intersect($userRoles)->isNotEmpty()) {
            return $next($request);
        }

        // Tidak sesuai → 403
        abort(403, 'Unauthorized — role tidak diizinkan.');
    }
}
