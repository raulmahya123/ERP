<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q',''));

        $users = User::query()
            ->with('role')
            ->when($q, fn($qq) => $qq->where(fn($w) =>
                $w->where('name','like',"%$q%")
                  ->orWhere('email','like',"%$q%")
            ))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users','q'));
    }

    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'name'     => ['required','string','max:100'],
            'email'    => ['required','email','max:190', Rule::unique('users','email')],
            'password' => ['required','min:6','confirmed'],
            'role_id'  => ['nullable', Rule::exists('roles','id')],
        ]);

        $data['password'] = Hash::make($data['password']);

        return DB::transaction(function () use ($data) {
            User::create($data);
            return redirect()->route('admin.users.index')->with('success','User dibuat.');
        });
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.users.edit', compact('user','roles'));
    }

    public function update(Request $req, User $user)
    {
        $data = $req->validate([
            'name'     => ['required','string','max:100'],
            'email'    => ['required','email','max:190', Rule::unique('users','email')->ignore($user->id)],
            'password' => ['nullable','min:6','confirmed'],
            'role_id'  => ['nullable', Rule::exists('roles','id')],
        ]);

        // Cegah menjatuhkan peran admin pada diri sendiri sehingga kehilangan akses admin
        if (auth()->id() === $user->id) {
            // contoh: larang ganti role menjadi null
            if (array_key_exists('role_id', $data) && is_null($data['role_id'])) {
                return back()->withErrors(['role_id' => 'Tidak bisa mengosongkan role akun sendiri.'])->withInput();
            }
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        return DB::transaction(function () use ($user, $data) {
            $user->update($data);
            return redirect()->route('admin.users.index')->with('success','User diperbarui.');
        });
    }

    public function destroy(User $user)
    {
        // Cegah hapus diri sendiri
        if (auth()->id() === $user->id) {
            return back()->withErrors(['user' => 'Tidak boleh menghapus akun sendiri.']);
        }

        $user->delete();
        return redirect()->route('admin.users.index')->with('success','User dihapus.');
    }
}
