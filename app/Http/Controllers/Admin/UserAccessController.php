<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use App\Models\Division;

class UserAccessController extends Controller
{
    public function __construct()
    {
        // Hanya GM (gate: grant-access) yang boleh kelola akses
        $this->middleware('can:grant-access');
    }

    /**
     * Daftar user + pencarian sederhana.
     * View: resources/views/admin/access/users/index.blade.php
     */
    public function index(Request $request)
    {
        $q = User::query()->with(['role','division']);

        if ($search = trim((string) $request->get('q', ''))) {
            $q->where(function ($w) use ($search) {
                $w->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $q->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.access.users.index', [
            'users'     => $users,
            'search'    => $search ?? '',
        ]);
    }

    /**
     * Form ubah akses 1 user (role & division).
     * View: resources/views/admin/access/user_role.blade.php
     */
    public function editRole(User $user)
    {
        return view('admin.access.user_role', [
            'user'      => $user->loadMissing(['role','division']),
            'roles'     => Role::orderBy('name')->get(),
            'divisions' => Division::orderBy('name')->get(),
        ]);
    }

    /**
     * Update akses 1 user (role & division).
     * Endpoint ini yang dipakai form editRole().
     */
    public function updateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_id'     => ['required','exists:roles,id'],
            'division_id' => ['nullable','exists:divisions,id'],
            // opsional: ganti password via form yg sama
            'password'    => ['nullable','string','min:8'],
        ]);

        // kalau password kosong, jangan di-update
        if (empty($validated['password'])) {
            unset($validated['password']);
        } else {
            $validated['password'] = bcrypt($validated['password']);
        }

        $user->update($validated);

        return back()->with('ok', 'Akses user diperbarui');
    }

    /**
     * (Opsional) Endpoint cepat via POST JSON — tanpa tampilan form.
     * Body:
     *  { "role_id": "...", "division_id": null }
     */
    public function apiUpdateRole(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_id'     => ['required','exists:roles,id'],
            'division_id' => ['nullable','exists:divisions,id'],
        ]);

        $user->update($validated);

        return response()->json([
            'ok'   => true,
            'msg'  => 'Role updated',
            'user' => $user->only(['id','name','email','role_id','division_id']),
        ]);
    }
}
