<?php

namespace App\Policies\Scm;

use App\Models\User;
use App\Models\Scm\Trip;
use Illuminate\Auth\Access\HandlesAuthorization;

class TripPolicy
{
    use HandlesAuthorization;

    /**
     * GM boleh semua.
     * Return null agar Laravel lanjut ke method lain bila tidak match.
     */
    public function before(User $user, string $ability): ?bool
    {
        if (method_exists($user, 'isGM') && $user->isGM()) {
            return true;
        }
        return null;
    }

    protected function sameSite(Trip $trip): bool
    {
        return (string) session('site_id') === (string) $trip->site_id;
    }

    public function viewAny(User $user): bool
    {
        return session()->has('site_id');
    }

    public function view(User $user, Trip $trip): bool
    {
        return $this->sameSite($trip);
    }

    public function create(User $user): bool
    {
        return session()->has('site_id');
    }

    public function update(User $user, Trip $trip): bool
    {
        return $this->sameSite($trip);
    }

    public function delete(User $user, Trip $trip): bool
    {
        return $this->sameSite($trip);
    }

    public function submit(User $user, Trip $trip): bool
    {
        return $this->sameSite($trip);
    }

    public function validate(User $user, Trip $trip): bool
    {
        return (method_exists($user, 'isManager') && $user->isManager())
            && $this->sameSite($trip);
    }

    public function approve(User $user, Trip $trip): bool
    {
        return (method_exists($user, 'isManager') && $user->isManager())
            && $this->sameSite($trip);
    }
}
