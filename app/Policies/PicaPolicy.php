<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Pica;

class PicaPolicy
{
    public function before(User $user, string $ability) { if ($user->isGM()) return true; }

    protected function allowed(User $user): bool
    { return in_array($user->role_key, ['gm','manager','hr','hse_officer'], true); }

    public function viewAny(User $user): bool { return $this->allowed($user); }
    public function view(User $user, Pica $pica): bool { return $this->allowed($user); }
    public function create(User $user): bool { return $this->allowed($user); }
    public function update(User $user, Pica $pica): bool { return $this->allowed($user); }
    public function delete(User $user, Pica $pica): bool
    { return in_array($user->role_key, ['gm','manager','hr'], true); }

    public function restore(User $user, Pica $pica): bool
    { return in_array($user->role_key, ['gm','manager','hr'], true); }

    public function forceDelete(User $user, Pica $pica): bool
    { return $user->role_key === 'gm'; }

    // Aksi workflow
    public function markEffective(User $user, Pica $pica): bool
    { return in_array($user->role_key, ['gm','manager','hse_officer','hr'], true); }

    public function markIneffective(User $user, Pica $pica): bool
    { return in_array($user->role_key, ['gm','manager','hse_officer','hr'], true); }

    public function close(User $user, Pica $pica): bool
    { return in_array($user->role_key, ['gm','manager','hr'], true); }
}
