<?php

namespace App\Policies;

use App\Models\User;
use App\Models\IncidentInvestigation;

class IncidentInvestigationPolicy
{
    public function before(User $user, string $ability) { if ($user->isGM()) return true; }

    protected function allowed(User $user): bool
    {
        return in_array($user->role_key, ['gm','manager','hr','hse_officer'], true);
    }

    public function viewAny(User $user): bool { return $this->allowed($user); }
    public function view(User $user, IncidentInvestigation $investigation): bool { return $this->allowed($user); }
    public function create(User $user): bool { return $this->allowed($user); }
    public function update(User $user, IncidentInvestigation $investigation): bool { return $this->allowed($user); }
    public function delete(User $user, IncidentInvestigation $investigation): bool
    { return in_array($user->role_key, ['gm','manager','hr'], true); }

    public function restore(User $user, IncidentInvestigation $investigation): bool
    { return in_array($user->role_key, ['gm','manager','hr'], true); }

    public function forceDelete(User $user, IncidentInvestigation $investigation): bool
    { return $user->role_key === 'gm'; }

    // Aksi kustom di routes
    public function complete(User $user, IncidentInvestigation $investigation): bool
    { return in_array($user->role_key, ['gm','manager','hse_officer'], true); }

    public function reopen(User $user, IncidentInvestigation $investigation): bool
    { return in_array($user->role_key, ['gm','manager','hse_officer'], true); }
}
