<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Division;
use App\Models\Site;        // NEW
use App\Models\SiteConfig;  // NEW
use Illuminate\Validation\Rule;

class UserAccessController extends Controller
{
    public function __construct()
    {
        // Hanya GM (gate: grant-access) yang boleh kelola akses
        $this->middleware('can:grant-access');
    }

    /**
     * Daftar user + pencarian sederhana (+ filter opsional).
     * View: resources/views/admin/access/users/index.blade.php
     */
    public function index(Request $request)
    {
        $q = User::query()
            ->with(['role','division','defaultSite']); // NEW

        if ($search = trim((string) $request->get('q', ''))) {
            $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter opsional
        if ($roleId = $request->get('role_id')) {
            $q->where('role_id', $roleId);
        }
        if ($divisionId = $request->get('division_id')) {
            $q->where('division_id', $divisionId);
        }
        if ($siteId = $request->get('site_id')) { // NEW
            $q->where('default_site_id', $siteId);
        }

        $users = $q->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.access.users.index', [
            'users'     => $users,
            'search'    => $search ?? '',
            // dropdowns (opsional)
            'roles'     => Role::orderBy('name')->get(),
            'divisions' => Division::orderBy('name')->get(),
            'sites'     => Site::orderBy('name')->get(), // NEW
        ]);
    }

    /**
     * Form ubah akses 1 user (role, division, default_site_id).
     * View: resources/views/admin/access/user_role.blade.php
     */
    public function editRole(User $user)
    {
        return view('admin.access.user_role', [
            'user'      => $user->loadMissing(['role','division','defaultSite']), // NEW
            'roles'     => Role::orderBy('name')->get(),
            'divisions' => Division::orderBy('name')->get(),
            'sites'     => Site::orderBy('name')->get(), // NEW
        ]);
    }

    /**
     * Update akses 1 user (role, division, default_site_id).
     * Endpoint ini yang dipakai form editRole().
     */
    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_id'         => ['required', Rule::exists('roles','id')],
            'division_id'     => ['nullable', Rule::exists('divisions','id')],
            'default_site_id' => ['nullable', Rule::exists('sites','id')], // NEW
            'password'        => ['nullable','string','min:8'],
        ]);

        // password kosong => jangan update
        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = bcrypt($validated['password']);
        }

        // Jika default_site_id tidak diisi → infer dari SiteConfig.default_for_users → fallback site pertama
        if (!array_key_exists('default_site_id', $validated) || is_null($validated['default_site_id'])) {
            $validated['default_site_id'] =
                SiteConfig::query()->whereJsonContains('params->default_for_users', true)->value('site_id')
                ?? Site::orderBy('name')->value('id');
        }

        $user->update($validated);

        return back()->with('ok', 'Akses user diperbarui');
    }

    /**
     * (Opsional) Endpoint cepat via POST JSON — tanpa tampilan form.
     * Body:
     *  { "role_id": "...", "division_id": null, "default_site_id": null }
     */
    public function apiUpdateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_id'         => ['required', Rule::exists('roles','id')],
            'division_id'     => ['nullable', Rule::exists('divisions','id')],
            'default_site_id' => ['nullable', Rule::exists('sites','id')], // NEW
        ]);

        if (!array_key_exists('default_site_id', $validated) || is_null($validated['default_site_id'])) {
            $validated['default_site_id'] =
                SiteConfig::query()->whereJsonContains('params->default_for_users', true)->value('site_id')
                ?? Site::orderBy('name')->value('id');
        }

        $user->update($validated);

        return response()->json([
            'ok'   => true,
            'msg'  => 'Role updated',
            'user' => $user->only(['id','name','email','role_id','division_id','default_site_id']), // NEW
        ]);
    }
}
