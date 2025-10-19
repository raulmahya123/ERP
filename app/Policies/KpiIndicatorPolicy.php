<?php

namespace App\Policies;

use App\Models\User;
use App\Models\KpiIndicator;

class KpiIndicatorPolicy
{
    public function before(User $user, string $ability) { if ($user->isGM()) return true; }

    protected function allowed(User $user): bool
    { return in_array($user->role_key, ['gm','manager','hr','hse_officer'], true); }

    public function viewAny(User $user): bool { return $this->allowed($user); }
    public function view(User $user, KpiIndicator $kpi): bool { return $this->allowed($user); }
    public function create(User $user): bool { return $this->allowed($user); }
    public function update(User $user, KpiIndicator $kpi): bool { return $this->allowed($user); }
    public function delete(User $user, KpiIndicator $kpi): bool
    { return in_array($user->role_key, ['gm','manager','hr'], true); }

    public function restore(User $user, KpiIndicator $kpi): bool
    { return in_array($user->role_key, ['gm','manager','hr'], true); }

    public function forceDelete(User $user, KpiIndicator $kpi): bool
    { return $user->role_key === 'gm'; }

    // Aksi tambahan (routes)
    public function export(User $user): bool
    { return $this->allowed($user); }

    public function import(User $user): bool
    { return in_array($user->role_key, ['gm','manager','hse_officer'], true); }
}
