<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Fuel\FuelFlowMeter;

class FuelFlowMeterPolicy
{
    public function before(User $user, string $ability) { if ($user->isGM()) return true; }
    protected function allowed(User $user): bool
    { return in_array($user->role_key, ['gm','manager','scm'], true); }
    public function viewAny(User $user): bool { return $this->allowed($user); }
    public function view(User $user, FuelFlowMeter $model): bool { return $this->allowed($user); }
    public function create(User $user): bool { return $this->allowed($user); }
    public function update(User $user, FuelFlowMeter $model): bool { return $this->allowed($user); }
    public function delete(User $user, FuelFlowMeter $model): bool { return $this->allowed($user); }
}
