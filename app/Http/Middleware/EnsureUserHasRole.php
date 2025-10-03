<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserHasRole
{
    /**
     * Pakai di route: ->middleware('hasrole:manager') atau 'hasrole:gm|manager'
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Belum login → arahkan ke login
        if (!Auth::check()) {
            return redirect()->guest(route('login'));
        }

        $user = Auth::user();

        // ===== 1) Normalisasi daftar role yang diizinkan (dukung koma & pipe) =====
        $allowed = collect($roles)
            ->flatMap(fn ($r) => preg_split('/[,\|]/', (string) $r)) // "gm|manager" atau "gm,manager"
            ->map(fn ($r) => strtolower(trim($r)))
            ->filter()
            ->unique()
            ->values();

        // Kalau tidak ada role yang dipassing, izinkan saja (opsional, tapi praktis)
        if ($allowed->isEmpty()) {
            return $next($request);
        }

        // ===== 2) Ambil semua role user (serba bisa) → lowercase =====
        $userRoles = collect();

        // 2a) Spatie (jika ada)
        try {
            if (method_exists($user, 'getRoleNames')) {
                $userRoles = $userRoles->merge(
                    $user->getRoleNames()->map(fn ($v) => strtolower($v))
                );
            }
        } catch (\Throwable $e) {}

        // 2b) Field string langsung: $user->role = 'gm'
        if (isset($user->role) && is_string($user->role)) {
            $userRoles->push(strtolower($user->role));
        }

        // 2c) Relasi tunggal: $user->role->(key|slug|name|title)
        if (isset($user->role) && is_object($user->role)) {
            foreach (['key','slug','name','title'] as $col) {
                if (!empty($user->role->{$col})) {
                    $userRoles->push(strtolower($user->role->{$col}));
                    break;
                }
            }
        }

        // 2d) Many-to-many: $user->roles()->pluck(...)
        if (method_exists($user, 'roles')) {
            foreach (['key','slug','name','title'] as $col) {
                try {
                    $vals = $user->roles()->pluck($col)->filter()->map(fn ($v) => strtolower($v));
                    if ($vals->isNotEmpty()) {
                        $userRoles = $userRoles->merge($vals);
                        break;
                    }
                } catch (\Throwable $e) { /* lanjut kolom berikut */ }
            }
        }

        $userRoles = $userRoles->filter()->unique()->values();

        // ===== 3) BYPASS: jika user adalah GM → selalu boleh =====
        if ($userRoles->contains('gm')) {
            return $next($request);
        }

        // ===== 4) Jika pakai Spatie, manfaatkan shortcut-nya =====
        if (method_exists($user, 'hasAnyRole')) {
            if ($user->hasAnyRole($allowed->all())) {
                return $next($request);
            }
        }

        // ===== 5) Cek manual =====
        if ($allowed->intersect($userRoles)->isNotEmpty()) {
            return $next($request);
        }

        // ===== 6) Tolak =====
        abort(403, 'Unauthorized — role tidak diizinkan.');
    }
}
