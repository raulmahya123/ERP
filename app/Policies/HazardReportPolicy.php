<?php

namespace App\Policies;

use App\Models\User;
use App\Models\HazardReport;

class HazardReportPolicy
{
    public function before(User $user, string $ability) { if ($user->isGM()) return true; }

    protected function allowed(User $user): bool
    { return in_array($user->role_key, ['gm','manager','hr','hse_officer'], true); }

    public function viewAny(User $user): bool { return $this->allowed($user); }
    public function view(User $user, HazardReport $hazard): bool { return $this->allowed($user); }
    public function create(User $user): bool { return $this->allowed($user); }
    public function update(User $user, HazardReport $hazard): bool { return $this->allowed($user); }
    public function delete(User $user, HazardReport $hazard): bool
    { return in_array($user->role_key, ['gm','manager','hr'], true); }

    public function restore(User $user, HazardReport $hazard): bool
    { return in_array($user->role_key, ['gm','manager','hr'], true); }

    public function forceDelete(User $user, HazardReport $hazard): bool
    { return $user->role_key === 'gm'; }

    // Aksi workflow (lihat routes)
    public function assign(User $user, HazardReport $hazard): bool
    { return in_array($user->role_key, ['gm','manager','hse_officer'], true); }

    public function mitigate(User $user, HazardReport $hazard): bool
    { return in_array($user->role_key, ['gm','manager','hse_officer'], true); }

    public function verify(User $user, HazardReport $hazard): bool
    { return in_array($user->role_key, ['gm','manager','hr','hse_officer'], true); }

    public function close(User $user, HazardReport $hazard): bool
    { return in_array($user->role_key, ['gm','manager','hr'], true); }
}
