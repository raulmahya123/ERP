<?php

namespace App\Policies;

use App\Models\{Location, LocationPermission, User};

class LocationPolicy
{
    private function gm(User $u): bool { return method_exists($u,'isGM') && $u->isGM(); }

    public function view(User $u, Location $l): bool {
        if ($this->gm($u) || $l->created_by === $u->id) return true;
        return LocationPermission::where('location_id',$l->id)
            ->where('user_id',$u->id)->where('can_view',true)->exists();
    }

    public function update(User $u, Location $l): bool {
        if ($this->gm($u) || $l->created_by === $u->id) return true;
        return LocationPermission::where('location_id',$l->id)
            ->where('user_id',$u->id)->where('can_update',true)->exists();
    }

    public function delete(User $u, Location $l): bool {
        if ($this->gm($u) || $l->created_by === $u->id) return true;
        return LocationPermission::where('location_id',$l->id)
            ->where('user_id',$u->id)->where('can_delete',true)->exists();
    }

    // GM/creator boleh atur share
    public function share(User $u, Location $l): bool {
        return $this->gm($u) || $l->created_by === $u->id;
    }
}
