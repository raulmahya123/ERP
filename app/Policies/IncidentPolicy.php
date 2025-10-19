<?php

namespace App\Policies;

use App\Models\Incident;
use App\Models\User;

class IncidentPolicy
{
    /** BYPASS: GM boleh semua */
    public function before(User $user, string $ability)
    {
        if ($user->isGM()) return true;
        return null;
    }

    protected function allowed(User $user): bool
    {
        return in_array($user->role_key, ['gm', 'manager', 'hr', 'hse_officer'], true);
    }

    public function viewAny(User $user): bool
    {
        return $this->allowed($user);
    }
    public function view(User $user, Incident $incident): bool
    {
        return $this->allowed($user);
    }
    public function create(User $user): bool
    {
        return $this->allowed($user);
    }
    public function update(User $user, Incident $incident): bool
    {
        return $this->allowed($user);
    }
    public function delete(User $user, Incident $incident): bool
    {
        return in_array($user->role_key, ['gm', 'manager', 'hr'], true);
    }
    public function restore(User $user, Incident $incident): bool
    {
        return in_array($user->role_key, ['gm', 'manager', 'hr'], true);
    }
    public function forceDelete(User $user, Incident $incident): bool
    {
        return $user->role_key === 'gm';
    }
}
