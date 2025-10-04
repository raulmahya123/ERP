<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class GateServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // === REGISTRASI POLICY UNTUK LARAVEL 12 ===
        Gate::policy(\App\Models\Location::class, \App\Policies\LocationPolicy::class);

        // Gate custom kamu tetap jalan
        Gate::define('manage-master-data', fn($user) => $this->isGm($user));
        Gate::define('grant-access',       fn($user) => $this->isGm($user));

        // === NEW (khusus GM) ===
        Gate::define('switch-site', fn($user) => $this->isGm($user));
        Gate::define('manage-site-config', fn($user) => $this->isGm($user));

        // Opsional: GM boleh lihat semua site (kalau mau dipakai di query builder)
        Gate::define('view-all-sites', fn($user) => $this->isGm($user));

        Gate::before(function ($user, $ability) {
            return (method_exists($user, 'hasRole') && $user->hasRole('super-admin')) ? true : null;
        });
    }

    private function isGm($user): bool
    {
        if (isset($user->role) && is_string($user->role) && mb_strtolower($user->role) === 'gm') return true;

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

        if (method_exists($user, 'hasRole') && $user->hasRole('gm')) return true;

        return false;
    }
}
