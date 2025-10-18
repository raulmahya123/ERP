<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class GateServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Gate::policy(\App\Models\Location::class, \App\Policies\LocationPolicy::class);

        Gate::define('manage-master-data', fn($user) => $this->isGm($user));
        Gate::define('grant-access',       fn($user) => $this->isGm($user));

        Gate::define('switch-site',        fn($user) => $this->isGm($user));
        Gate::define('manage-site-config', fn($user) => $this->isGm($user));
        Gate::define('view-all-sites',     fn($user) => $this->isGm($user));

        Gate::define('master.access', function ($user, string $entity): bool {
            if ($this->isGm($user)) return true;

            $entity = strtolower(preg_replace('/[^a-z0-9_]/i', '', $entity) ?: '');

            if ($this->hasPerm($user, [
                'master.manage',
                "master.{$entity}.manage",
                "master.{$entity}.view",
            ])) return true;

            return $this->hasCustomMatrix($user->id, $entity);
        });

        Gate::define('assets.view', function ($user): bool {
            if ($this->isGm($user)) return true;
            return $this->hasPerm($user, ['assets.view', 'assets.manage']);
        });

        Gate::define('assets.manage', function ($user): bool {
            if ($this->isGm($user)) return true;
            return $this->hasPerm($user, ['assets.manage']);
        });

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

    private function hasPerm($user, array $perms): bool
    {
        if (method_exists($user, 'hasAnyPermission') && $user->hasAnyPermission($perms)) return true;
        if (method_exists($user, 'can')) {
            foreach ($perms as $p) if ($user->can($p)) return true;
        }
        return false;
    }

    private function hasCustomMatrix(string $userId, string $entity): bool
    {
        $candidates = ['master_entity_permissions', 'master_permissions', 'entity_permissions'];

        foreach ($candidates as $table) {
            if (!Schema::hasTable($table)) continue;

            $columns  = Schema::getColumnListing($table);
            $colUser  = in_array('user_id', $columns, true) ? 'user_id' : null;
            $colEnt   = in_array('entity', $columns, true) ? 'entity' : (in_array('entity_key', $columns, true) ? 'entity_key' : null);
            if (!$colUser || !$colEnt) continue;

            $flags = array_values(array_intersect(
                ['view','can_view','read','download','can_download','update','can_update','edit','delete','can_delete','manage'],
                $columns
            ));
            if (!$flags) continue;

            $row = DB::table($table)->where($colUser, $userId)->where($colEnt, $entity)->first($flags);
            if ($row) foreach ($flags as $f) if (!empty($row->$f)) return true;
        }

        if (Schema::hasTable('master_record_permissions') && Schema::hasTable('master_records')) {
            $permCols = Schema::getColumnListing('master_record_permissions');
            $flags = array_values(array_intersect(
                ['view','can_view','read','download','can_download','update','can_update','edit','delete','can_delete','manage'],
                $permCols
            ));

            if ($flags) {
                $recCols = Schema::getColumnListing('master_records');
                $entityCol = in_array('entity', $recCols, true) ? 'entity' : (in_array('entity_key', $recCols, true) ? 'entity_key' : null);

                if ($entityCol) {
                    $row = DB::table('master_record_permissions as p')
                        ->join('master_records as r', 'r.id', '=', 'p.master_record_id')
                        ->where('p.user_id', $userId)
                        ->where("r.$entityCol", $entity)
                        ->select(array_map(fn($f) => "p.$f", $flags))
                        ->first();

                    if ($row) foreach ($flags as $f) if (!empty($row->$f)) return true;
                }
            }
        }

        return false;
    }
}
