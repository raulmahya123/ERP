<?php

// app/Policies/HrDailyEntryPolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\HrDailyEntry;

class HrDailyEntryPolicy
{
    public function approve(User $user, HrDailyEntry $entry): bool
    {
        $key = strtolower($user->role->key ?? '');
        return in_array($key, ['gm','manager','hr'], true);
    }

    public function reject(User $user, HrDailyEntry $entry): bool
    {
        $key = strtolower($user->role->key ?? '');
        return in_array($key, ['gm','manager','hr'], true);
    }

    // ⬇️ ini yang make sure menu HR Config muncul sesuai role
    public function manage(User $user): bool
    {
        $key = strtolower($user->role->key ?? '');
        return in_array($key, ['gm','manager','hr'], true);
    }
}
