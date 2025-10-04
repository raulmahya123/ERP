<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class GateServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::define('manage-master-data', fn($user) => $this->isGm($user));
        Gate::define('grant-access',        fn($user) => $this->isGm($user));

        // Opsional: super-admin tembus semua
        Gate::before(function ($user, $ability) {
            return (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) ? true : null;
        });
    }

    private function isGm($user): bool
    {
        // JANGAN menyentuh roles(); hanya role() tunggal + varian lain
        // Field string
        if (isset($user->role) && is_string($user->role) && mb_strtolower($user->role) === 'gm') {
            return true;
        }

        // Relasi tunggal
        if (method_exists($user, 'role')) {
            try { $user->loadMissing('role'); } catch (\Throwable $e) {}
            $vals = [
                mb_strtolower($user->role->key   ?? ''),
                mb_strtolower($user->role->slug  ?? ''),
                mb_strtolower($user->role->name  ?? ''),
                mb_strtolower($user->role->title ?? ''),
            ];
            if (in_array('gm', $vals, true)) return true;
        }

        // Spatie (kalau ada)
        if (method_exists($user, 'hasRole') && $user->hasRole('gm')) {
            return true;
        }

        return false;
    }
}
