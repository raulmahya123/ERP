<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Plant\PlantBreakdownStatus;

class PlantBreakdownStatusPolicy
{
    public function before(User $user, string $ability) { if ($user->isGM()) return true; }
    protected function allowed(User $user): bool
    { return in_array($user->role_key, ['gm','manager'], true); }
    public function viewAny(User $user): bool { return $this->allowed($user); }
    public function view(User $user, PlantBreakdownStatus $model): bool { return $this->allowed($user); }
    public function create(User $user): bool { return $this->allowed($user); }
    public function update(User $user, PlantBreakdownStatus $model): bool { return $this->allowed($user); }
    public function delete(User $user, PlantBreakdownStatus $model): bool { return $this->allowed($user); }
}
