<?php

namespace App\Policies;

use App\Models\HrDailyEntry;
use App\Models\SiteConfig;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class HrDailyEntryPolicy
{
    use HandlesAuthorization;

    /* =========================
     | Helpers role
     |=========================*/
    protected function roleKey(User $user): string
    {
        $raw = $user->role_key
            ?? ($user->role->key ?? $user->role->slug ?? $user->role->name ?? '');
        $norm = Str::of((string)$raw)->lower()->replace(['_', '-'], ' ')->squish()->toString();
        return $norm;
    }

    protected function isGm(User $u): bool
    {
        return $this->roleKey($u) === 'gm';
    }

    protected function isGmHrMgr(User $u): bool
    {
        return in_array($this->roleKey($u), ['gm', 'hr', 'manager'], true);
    }

    protected function isGmHrMgrHse(User $u): bool
    {
        $rk = $this->roleKey($u);
        return in_array($rk, ['gm', 'hr', 'manager', 'hse officer', 'hse', 'health safety environment'], true);
    }

    /* =========================
     | Load HR params dari SiteConfig
     |=========================*/
    protected function hrParams(?string $siteId = null): array
    {
        if (!Schema::hasTable('site_configs')) return [];
        $q = SiteConfig::query();
        if ($siteId) $q->where('site_id', $siteId);
        $row = $q->oldest('created_at')->first();
        if (!$row) return [];
        $params = (array)($row->params ?? []);
        return (array)($params['hr'] ?? []);
    }

    protected function configKey(string $suffix, array $hr): string
    {
        $map = (array)($hr['config_keys'] ?? []);
        $fallback = [
            'approval_schemas' => 'entry_approval_schemas',
        ];
        return (string)($map[$suffix] ?? $fallback[$suffix] ?? ('entry_' . $suffix));
    }

    /** Ambil approval flow (array stages) untuk type & site */
    protected function approvalFlowFor(?string $type, ?string $siteId): array
    {
        $hr   = $this->hrParams($siteId);
        $key  = $this->configKey('approval_schemas', $hr);
        $all  = (array)($hr[$key] ?? []);
        $cfg  = (array)($all[(string)$type] ?? []);
        $stages = array_values((array)($cfg['stages'] ?? []));
        // minimal normalisasi
        return array_map(function ($s) {
            return [
                'key'              => (string)($s['key'] ?? ''),
                'label'            => (string)($s['label'] ?? ''),
                'roles'            => array_values((array)($s['roles'] ?? [])), // role_id list
                'all_must_approve' => (bool)($s['all_must_approve'] ?? false),
            ];
        }, $stages);
    }

    /* =========================
     | Abilities
     |=========================*/

    /** Bisa lihat daftar (index) */
    public function viewAny(User $user): bool
    {
        // GM, HR, Manager, HSE boleh melihat
        return $this->isGmHrMgrHse($user);
    }

    /** Lihat satu entry */
    public function view(User $user, HrDailyEntry $entry): bool
    {
        // sama dengan viewAny; optionally tambahkan pembatasan site
        return $this->viewAny($user);
    }

    /** CRUD konfigurasi HR (schema, print, dll) */
    public function manage(User $user): bool
    {
        // batasi untuk GM & HR saja (manager tidak)
        return in_array($this->roleKey($user), ['gm', 'hr'], true);
    }

    /** Kirim untuk approval */
    public function submit(User $user, HrDailyEntry $entry): bool
    {
        // siapa pun yang punya akses modul, boleh submit (sesuaikan jika ingin hanya HR)
        return $this->isGmHrMgrHse($user);
    }

    /** Approve: cek apakah role user termasuk role stage aktif */
    public function approve(User $user, HrDailyEntry $entry): bool
    {
        // GM selalu boleh
        if ($this->isGm($user)) return true;

        $flow = $this->approvalFlowFor($entry->type, $entry->site_id);
        if (empty($flow)) {
            // jika belum ada schema, fallback: HR & Manager boleh approve
            return in_array($this->roleKey($user), ['hr', 'manager'], true);
        }

        $trail = (array)($entry->meta['_approval'] ?? []);
        $idx   = (int)($trail['current_index'] ?? 0);
        $stage = (array)($flow[$idx] ?? []);

        $userRoleId = (string)($user->role_id ?? '');
        if ($userRoleId === '') return false;

        $roles = array_map('strval', (array)($stage['roles'] ?? []));
        return in_array($userRoleId, $roles, true);
    }

    /** Reject mengikuti syarat yang sama dengan approve */
    public function reject(User $user, HrDailyEntry $entry): bool
    {
        return $this->approve($user, $entry);
    }

    /** Update entry */
    public function update(User $user, HrDailyEntry $entry): bool
    {
        return in_array($this->roleKey($user), ['gm', 'hr', 'manager'], true);
    }

    /** Hapus entry */
    public function delete(User $user, HrDailyEntry $entry): bool
    {
        return in_array($this->roleKey($user), ['gm', 'hr'], true);
    }

    public function restore(User $user, HrDailyEntry $entry): bool
    {
        return in_array($this->roleKey($user), ['gm', 'hr'], true);
    }

    public function forceDelete(User $user, HrDailyEntry $entry): bool
    {
        return $this->isGm($user);
    }
}
