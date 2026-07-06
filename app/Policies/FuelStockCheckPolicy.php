<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Fuel\FuelStockCheck;

class FuelStockCheckPolicy
{
    public function before(User $user, string $ability) { if ($user->isGM()) return true; }
    protected function allowed(User $user): bool
    { return in_array($user->role_key, ['gm','manager','scm'], true); }
    public function viewAny(User $user): bool { return $this->allowed($user); }
    public function view(User $user, FuelStockCheck $model): bool { return $this->allowed($user); }
    public function create(User $user): bool { return $this->allowed($user); }
    public function update(User $user, FuelStockCheck $model): bool { return $this->allowed($user); }
    public function delete(User $user, FuelStockCheck $model): bool { return $this->allowed($user); }
}
