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
        // 0) Guest handling
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->guest(route('login'));
        }

        $user = Auth::user();

        // 1) Normalisasi daftar role yang diizinkan (dukung koma & pipe), lowercase
        $allowed = collect($roles)
            ->flatMap(fn ($r) => preg_split('/[,\|]/', (string) $r) ?: [])
            ->map(fn ($r) => mb_strtolower(trim($r)))
            ->filter()
            ->unique()
            ->values();

        if ($allowed->isEmpty()) {
            return $next($request); // tanpa parameter = izinkan
        }

        // 2) Eager load relasi yang BENAR-BENAR ada
        $relations = ['role']; // single-role
        if (method_exists($user, 'roles')) {
            // hanya kalau model mendefinisikan relasi many-to-many roles()
            $relations[] = 'roles:id,key,slug,name,title';
        }
        $user->loadMissing($relations);

        // 3) Kumpulkan semua role user (lowercase multibyte)
        $userRoles = collect();

        // 3a) Spatie (kalau trait HasRoles dipakai)
        try {
            if (method_exists($user, 'getRoleNames')) {
                $userRoles = $userRoles->merge(
                    $user->getRoleNames()->map(fn ($v) => mb_strtolower($v))
                );
            }
        } catch (\Throwable $e) {
            // ignore
        }

        // 3b) Field string langsung (kalau kolom 'role' berupa string)
        if (isset($user->role) && is_string($user->role)) {
            $userRoles->push(mb_strtolower($user->role));
        }

        // 3c) Relasi tunggal role()
        if (isset($user->role) && is_object($user->role)) {
            foreach (['key', 'slug', 'name', 'title'] as $col) {
                $val = $user->role->{$col} ?? null;
                if (is_string($val) && $val !== '') {
                    $userRoles->push(mb_strtolower($val));
                    break;
                }
            }
        }

        // 3d) Many-to-many roles() (hanya jika relasinya ada & sudah diload)
        if (method_exists($user, 'roles') && $user->relationLoaded('roles')) {
            $mm = $user->roles->map(function ($r) {
                foreach (['key', 'slug', 'name', 'title'] as $col) {
                    $val = $r->{$col} ?? null;
                    if (is_string($val) && $val !== '') {
                        return mb_strtolower($val);
                    }
                }
                return null;
            })->filter();
            $userRoles = $userRoles->merge($mm);
        }

        $userRoles = $userRoles->filter()->unique()->values();

        // 4) BYPASS: GM selalu boleh
        if ($userRoles->contains('gm')) {
            return $next($request);
        }

        // 5) Spatie shortcut (kalau ada)
        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($allowed->all())) {
            return $next($request);
        }

        // 6) Cek manual
        if ($allowed->intersect($userRoles)->isNotEmpty()) {
            return $next($request);
        }

        // 7) Tolak — format JSON vs web
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Forbidden — role tidak diizinkan.'], 403);
        }
        abort(403, 'Unauthorized — role tidak diizinkan.');
    }
}
