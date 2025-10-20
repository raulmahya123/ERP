<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Scm\Breakdown;

class BreakdownPolicy
{
    // Bisa dipakai Gate::policy(Breakdown::class, BreakdownPolicy::class)
    public function viewAny(User $user): bool
    {
        // role kasar: gm/manager/scm/foreman/operator bisa lihat
        $role = strtolower($user->role_key ?? $user->role->key ?? '');
        return in_array($role, ['gm','manager','scm','foreman','operator']);
    }

    public function view(User $user, Breakdown $bd): bool
    {
        $role = strtolower($user->role_key ?? $user->role->key ?? '');
        if (in_array($role, ['gm','manager'])) return true;
        // cek site dari session (site context)
        $sessionSite = session('site_id');
        return $sessionSite && $sessionSite === $bd->site_id;
    }

    public function create(User $user): bool { return $this->viewAny($user); }
    public function update(User $user, Breakdown $bd): bool { return $this->view($user, $bd); }
    public function delete(User $user, Breakdown $bd): bool { return $this->view($user, $bd); }
}
