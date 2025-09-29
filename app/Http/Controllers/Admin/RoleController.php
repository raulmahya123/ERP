<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    // Role yang dilindungi (tidak boleh dihapus/diubah key-nya)
    private const PROTECTED_KEYS = ['gm','manager','foreman','operator','hse_officer','hr','finance'];

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $roles = Role::query()
            ->when($q, fn($qq) => $qq->where(fn($w) =>
                $w->where('name','like',"%$q%")
                  ->orWhere('key','like',"%$q%")
            ))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.roles.index', compact('roles','q'));
    }

    public function create()
    {
        return view('admin.roles.create');
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'key'         => ['required','alpha_dash','max:50', Rule::unique('roles','key')],
            'name'        => ['required','string','max:100'],
            'description' => ['nullable','string','max:1000'],
        ]);

        // Normalisasi key ke lowercase
        $data['key'] = strtolower($data['key']);

        return DB::transaction(function () use ($data) {
            Role::create($data);
            return redirect()->route('admin.roles.index')->with('success','Role dibuat.');
        });
    }

    public function edit(Role $role)
    {
        return view('admin.roles.edit', compact('role'));
    }

    public function update(Request $req, Role $role)
    {
        $data = $req->validate([
            'key'         => ['required','alpha_dash','max:50', Rule::unique('roles','key')->ignore($role->id)],
            'name'        => ['required','string','max:100'],
            'description' => ['nullable','string','max:1000'],
        ]);

        // Jika role termasuk protected, kunci key-nya tidak boleh diubah
        if (in_array($role->key, self::PROTECTED_KEYS, true) && strtolower($data['key']) !== $role->key) {
            return back()->withErrors(['key' => 'Key role ini dilindungi dan tidak boleh diubah.'])->withInput();
        }

        $data['key'] = strtolower($data['key']);

        return DB::transaction(function () use ($role, $data) {
            $role->update($data);
            return redirect()->route('admin.roles.index')->with('success','Role diperbarui.');
        });
    }

    public function destroy(Role $role)
    {
        // Lindungi role bawaan
        if (in_array($role->key, self::PROTECTED_KEYS, true)) {
            return back()->withErrors(['role' => 'Role ini dilindungi dan tidak boleh dihapus.']);
        }

        // Jika foreign key di users diset nullOnDelete, ini aman;
        // tapi kalau mau cegah penghapusan saat masih dipakai:
        // if ($role->users()->exists()) return back()->withErrors(['role'=>'Masih dipakai user.']);

        $role->delete();
        return redirect()->route('admin.roles.index')->with('success','Role dihapus.');
    }
}
