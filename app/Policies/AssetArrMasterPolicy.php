<?php

namespace App\Policies;

use App\Models\AssetMgmt\AssetArrMaster;
use App\Models\User;

class AssetArrMasterPolicy
{
    public function before(User $user, string $ability) { if ($user->isGM()) return true; }
    protected function allowed(User $user): bool
    { return in_array($user->role_key, ['gm','manager'], true); }
    public function viewAny(User $user): bool { return $this->allowed($user); }
    public function view(User $user, AssetArrMaster $model): bool { return $this->allowed($user); }
    public function create(User $user): bool { return $this->allowed($user); }
    public function update(User $user, AssetArrMaster $model): bool { return $this->allowed($user); }
    public function delete(User $user, AssetArrMaster $model): bool { return $this->allowed($user); }
}
