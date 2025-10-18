<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Asset;

class AssetPolicy
{
    /**
     * Akses index/list Assets.
     * Lolos jika:
     * - role gm|manager, ATAU
     * - punya permission (Spatie) assets.view / assets.manage, ATAU
     * - Gate/ability bawaan: assets.view
     */
    public function viewAny(User $user): bool
    {
        return $this->hasAccess($user);
    }

    public function view(User $user, Asset $asset): bool
    {
        return $this->hasAccess($user);
    }

    public function create(User $user): bool
    {
        return $this->hasAccess($user);
    }

    public function update(User $user, Asset $asset): bool
    {
        return $this->hasAccess($user);
    }

    public function delete(User $user, Asset $asset): bool
    {
        return $this->hasAccess($user);
    }

    private function hasAccess(User $user): bool
    {
        // 1) Cek role (sesuai middleware lama)
        $roleKey = strtolower($user->role_key ?? $user->role?->key ?? $user->role?->slug ?? $user->role?->name ?? '');
        if (in_array($roleKey, ['gm', 'manager'], true)) {
            return true;
        }

        // 2) Jika pakai Spatie\Permission → hormati permission
        if (method_exists($user, 'hasAnyPermission')) {
            if ($user->hasAnyPermission(['assets.manage', 'assets.view'])) {
                return true;
            }
        }

        // 3) Jika ada Gate/ability custom di user->can()
        if (method_exists($user, 'can') && $user->can('assets.view')) {
            return true;
        }

        return false;
    }
}
