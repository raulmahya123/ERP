<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Hse\HseRtp;

class HseRtpPolicy
{
    public function before(User $user, string $ability) { if ($user->isGM()) return true; }

    protected function allowed(User $user): bool
    { return in_array($user->role_key, ['gm','manager','hr','hse_officer'], true); }

    public function viewAny(User $user): bool { return $this->allowed($user); }
    public function view(User $user, HseRtp $hseRtp): bool { return $this->allowed($user); }
    public function create(User $user): bool { return $this->allowed($user); }
    public function update(User $user, HseRtp $hseRtp): bool { return $this->allowed($user); }
    public function delete(User $user, HseRtp $hseRtp): bool { return $this->allowed($user); }
}
