<?php

namespace App\Policies;

use App\Models\Hcm\RecruitmentManpowerRequest;
use App\Models\User;

class RecruitmentManpowerRequestPolicy
{
    public function before(User $user, string $ability) { if ($user->isGM()) return true; }
    protected function allowed(User $user): bool
    { return in_array($user->role_key, ['gm','hr'], true); }
    public function viewAny(User $user): bool { return $this->allowed($user); }
    public function view(User $user, RecruitmentManpowerRequest $model): bool { return $this->allowed($user); }
    public function create(User $user): bool { return $this->allowed($user); }
    public function update(User $user, RecruitmentManpowerRequest $model): bool { return $this->allowed($user); }
    public function delete(User $user, RecruitmentManpowerRequest $model): bool { return $this->allowed($user); }
}
