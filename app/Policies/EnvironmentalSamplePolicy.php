<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\EnvironmentalSample;
use Illuminate\Auth\Access\Response;

final class EnvironmentalSamplePolicy
{
    /**
     * GM bypass semua ability.
     */
    public function before(User $user, string $ability): ?bool
    {
        if (method_exists($user, 'isGM') && $user->isGM()) {
            return true;
        }
        return null;
    }

    /**
     * Role yang boleh akses modul ini secara umum.
     */
    private function allowed(User $user): bool
    {
        $key = (string) ($user->role_key ?? '');
        return in_array($key, ['gm','manager','hr','hse_officer'], true);
    }

    /**
     * Harus di site yang sama dengan session (kecuali record tanpa site_id).
     */
    private function sameSite(EnvironmentalSample $sample): bool
    {
        if ($sample->site_id === null) {
            return true; // record orphan → boleh (opsional; ubah ke false jika mau super ketat)
        }
        $currentSiteId = session('site_id');
        if (!is_string($currentSiteId) || $currentSiteId === '') {
            return false; // tidak ada konteks site aktif → tolak
        }
        return $sample->site_id === $currentSiteId;
    }

    public function viewAny(User $user): Response|bool
    {
        return $this->allowed($user)
            ? Response::allow()
            : Response::deny('RBAC: role tidak diizinkan melihat daftar Environmental Samples.');
    }

    public function view(User $user, EnvironmentalSample $sample): Response|bool
    {
        if (!$this->allowed($user)) {
            return Response::deny('RBAC: role tidak diizinkan melihat data ini.');
        }
        return $this->sameSite($sample)
            ? Response::allow()
            : Response::deny('RBAC: site aktif tidak sesuai dengan site data.');
    }

    public function create(User $user): Response|bool
    {
        return $this->allowed($user)
            ? Response::allow()
            : Response::deny('RBAC: role tidak diizinkan membuat Environmental Sample.');
    }

    public function update(User $user, EnvironmentalSample $sample): Response|bool
    {
        if (!$this->allowed($user)) {
            return Response::deny('RBAC: role tidak diizinkan mengubah Environmental Sample.');
        }
        return $this->sameSite($sample)
            ? Response::allow()
            : Response::deny('RBAC: site aktif tidak sesuai dengan site data.');
    }

    public function delete(User $user, EnvironmentalSample $sample): Response|bool
    {
        $key = (string) ($user->role_key ?? '');
        if (!in_array($key, ['gm','manager','hr'], true)) {
            return Response::deny('RBAC: hanya GM/Manager/HR yang boleh menghapus.');
        }
        return $this->sameSite($sample)
            ? Response::allow()
            : Response::deny('RBAC: site aktif tidak sesuai dengan site data.');
    }

    public function restore(User $user, EnvironmentalSample $sample): Response|bool
    {
        $key = (string) ($user->role_key ?? '');
        if (!in_array($key, ['gm','manager','hr'], true)) {
            return Response::deny('RBAC: hanya GM/Manager/HR yang boleh restore.');
        }
        return $this->sameSite($sample)
            ? Response::allow()
            : Response::deny('RBAC: site aktif tidak sesuai dengan site data.');
    }

    public function forceDelete(User $user, EnvironmentalSample $sample): Response|bool
    {
        return ($user->role_key ?? null) === 'gm'
            ? Response::allow()
            : Response::deny('RBAC: hanya GM yang boleh force delete.');
    }
}
