<?php

namespace App\Policies;

use App\Models\User;
use App\Models\HrDailyEntry;

class HrDailyEntryPolicy
{
    /**
     * GM auto-allow semua ability (kecuali return null untuk fallback ke rule spesifik).
     */
    public function before(User $user, string $ability)
    {
        $key = strtolower($user->role->key ?? '');
        if ($key === 'gm') {
            return true;
        }
        return null;
    }

    /** =========================
     *  Helper kecil
     * ========================= */
    protected function roleIn(User $user, array $allowed): bool
    {
        $key = strtolower($user->role->key ?? '');
        return in_array($key, $allowed, true);
    }

    /** =========================
     *  Listing / akses umum
     * ========================= */
    public function viewAny(User $user): bool
    {
        // Boleh lihat index untuk HR & Manager (GM sudah auto via before)
        return $this->roleIn($user, ['hr','manager']);
    }

    public function view(User $user, HrDailyEntry $entry): bool
    {
        // Lihat detail/attachment: pemilik atau HR/Manager
        if ($entry->user_id && $entry->user_id === $user->id) {
            return true;
        }
        return $this->roleIn($user, ['hr','manager']);
    }

    /** =========================
     *  Config & Meta (menu HR Config)
     * ========================= */
    public function manage(User $user): bool
    {
        return $this->roleIn($user, ['hr','manager']);
    }

    /** =========================
     *  Export / Print (class-based ability)
     *  Dipakai: can:export, App\Models\HrDailyEntry
     * ========================= */
    public function export(User $user): bool
    {
        return $this->roleIn($user, ['hr','manager']);
    }

    /** =========================
     *  Flow Approval
     *  Dipakai: can:submit,entry ; can:approve,entry ; can:reject,entry
     * ========================= */
    public function submit(User $user, HrDailyEntry $entry): bool
    {
        // Pemilik boleh submit, HR/Manager juga boleh submitkan (misal proxy)
        if ($entry->user_id && $entry->user_id === $user->id) {
            return true;
        }
        return $this->roleIn($user, ['hr','manager']);
    }

    public function approve(User $user, HrDailyEntry $entry): bool
    {
        return $this->roleIn($user, ['manager','hr']);
    }

    public function reject(User $user, HrDailyEntry $entry): bool
    {
        return $this->roleIn($user, ['manager','hr']);
    }

    /** =========================
     *  Bulk actions
     *  Dipakai: can:bulkAction, App\Models\HrDailyEntry
     * ========================= */
    public function bulkAction(User $user): bool
    {
        return $this->roleIn($user, ['hr','manager']);
    }

    /** =========================
     *  Soft delete / trash
     *  Dipakai: can:viewTrashed, App\Models\HrDailyEntry
     * ========================= */
    public function viewTrashed(User $user): bool
    {
        return $this->roleIn($user, ['hr','manager']);
    }

    /** =========================
     *  Restore & Force Delete (instance-based)
     *  Dipakai: can:restore,entry ; can:forceDelete,entry
     * ========================= */
    public function restore(User $user, HrDailyEntry $entry): bool
    {
        return $this->roleIn($user, ['hr','manager']);
    }

    public function forceDelete(User $user, HrDailyEntry $entry): bool
    {
        return $this->roleIn($user, ['hr','manager']);
    }

    /** =========================
     *  Create / Update / Delete (opsional, jika dipakai)
     * ========================= */
    public function create(User $user): bool
    {
        // Karyawan boleh buat untuk dirinya, HR/Manager juga boleh
        return true;
    }

    public function update(User $user, HrDailyEntry $entry): bool
    {
        if ($entry->user_id && $entry->user_id === $user->id) {
            return true;
        }
        return $this->roleIn($user, ['hr','manager']);
    }

    public function delete(User $user, HrDailyEntry $entry): bool
    {
        if ($entry->user_id && $entry->user_id === $user->id) {
            return true;
        }
        return $this->roleIn($user, ['hr','manager']);
    }
}
