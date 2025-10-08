<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Division;
use App\Models\Site;         // <— tambah
use App\Models\SiteConfig;   // <— tambah
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $q          = trim((string) $request->get('q', ''));
        $roleId     = $request->get('role_id');
        $divisionId = $request->get('division_id');
        $siteId     = $request->get('site_id');               // NEW
        $perPage    = (int) $request->get('per_page', 20);    // NEW
        $perPage    = max(5, min($perPage, 100));             // clamp 5..100

        $users = User::query()
            ->with(['role', 'division', 'defaultSite'])         // ORM eager load
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                        ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->when($roleId,     fn($qq) => $qq->where('role_id', $roleId))
            ->when($divisionId, fn($qq) => $qq->where('division_id', $divisionId))

            // ===== PILIH SALAH SATU SESUAI MODEL =====
            // (A) Jika kamu punya kolom default_site_id (belongsTo defaultSite):
            ->when($siteId, fn($qq) => $qq->where('default_site_id', $siteId))

            // (B) Jika user bisa punya banyak site (many-to-many: users_sites):
            // ->when($siteId, fn($qq) => $qq->whereHas('sites', fn($s) => $s->where('sites.id', $siteId)))

            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();

        $roles     = Role::orderBy('name')->get();
        $divisions = Division::orderBy('name')->get();
        $sites     = Site::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'q', 'roles', 'divisions', 'sites'));
    }


    public function create()
    {
        $roles     = Role::orderBy('name')->get();
        $divisions = Division::orderBy('name')->get();
        $sites     = Site::orderBy('name')->get(); // <—
        return view('admin.users.create', compact('roles', 'divisions', 'sites'));
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'name'            => ['required', 'string', 'max:100'],
            'email'           => ['required', 'email', 'max:190', Rule::unique('users', 'email')],
            'password'        => ['required', 'min:6', 'confirmed'],
            'role_id'         => ['nullable', Rule::exists('roles', 'id')],
            'division_id'     => ['nullable', Rule::exists('divisions', 'id')],
            'default_site_id' => ['nullable', Rule::exists('sites', 'id')], // <—
        ]);

        // Tentukan default_site_id kalau kosong → dari SiteConfig.params->default_for_users = true → fallback site pertama
        $data['default_site_id'] = $data['default_site_id']
            ?? SiteConfig::query()
            ->whereJsonContains('params->default_for_users', true)
            ->value('site_id')
            ?? Site::orderBy('name')->value('id');

        $data['password'] = Hash::make($data['password']);

        try {
            DB::transaction(function () use ($data) {
                User::create($data);
            });
            return redirect()->route('admin.users.index')->with('success', 'User dibuat.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal membuat user.')->withInput();
        }
    }

    public function edit(User $user)
    {
        $roles     = Role::orderBy('name')->get();
        $divisions = Division::orderBy('name')->get();
        $sites     = Site::orderBy('name')->get(); // <—
        return view('admin.users.edit', compact('user', 'roles', 'divisions', 'sites'));
    }

    public function update(Request $req, User $user)
    {
        $data = $req->validate([
            'name'            => ['required', 'string', 'max:100'],
            'email'           => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'password'        => ['nullable', 'min:6', 'confirmed'],
            'role_id'         => ['nullable', Rule::exists('roles', 'id')],
            'division_id'     => ['nullable', Rule::exists('divisions', 'id')],
            'default_site_id' => ['nullable', Rule::exists('sites', 'id')], // <—
        ]);

        // Prevent user mengosongkan role sendiri
        if (auth()->id() === $user->id && array_key_exists('role_id', $data) && is_null($data['role_id'])) {
            return back()
                ->with('error', 'Tidak bisa mengosongkan role akun sendiri.')
                ->withErrors(['role_id' => 'Tidak bisa mengosongkan role akun sendiri.'])
                ->withInput();
        }

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Jika default_site_id tidak diisi, infer seperti di store()
        if (!array_key_exists('default_site_id', $data) || is_null($data['default_site_id'])) {
            $data['default_site_id'] =
                SiteConfig::query()->whereJsonContains('params->default_for_users', true)->value('site_id')
                ?? Site::orderBy('name')->value('id');
        }

        try {
            DB::transaction(function () use ($user, $data) {
                $user->update($data);
            });
            return redirect()->route('admin.users.index')->with('success', 'User diperbarui.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal memperbarui user.')->withInput();
        }
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()
                ->with('error', 'Tidak boleh menghapus akun sendiri.')
                ->withErrors(['user' => 'Tidak boleh menghapus akun sendiri.']);
        }

        try {
            $user->delete();
            return redirect()->route('admin.users.index')->with('success', 'User dihapus.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Gagal menghapus user.');
        }
    }

    public function show(User $user)
    {
        $user->load(['role', 'division', 'defaultSite']); // <—
        return view('admin.users.show', compact('user'));
    }

    public function resetPassword(User $user)
    {
        // password sementara (boleh ganti panjang/kompleksitas sesuai kebijakan)
        $temp = Str::random(12);

        try {
            $user->forceFill([
                'password' => Hash::make($temp),
            ])->save();

            // Flash biasa + payload khusus untuk popup di view
            return back()->with([
                'success'         => "Password user {$user->name} telah direset.",
                'reset_password'  => [
                    'user'     => $user->name,
                    'password' => $temp,
                ],
            ]);
        } catch (\Throwable $e) {
            // optional: report($e);
            return back()->with('error', 'Gagal mereset password.');
        }
    }

    public function export()
    {
        $filename = 'users-' . now()->format('Ymd-His') . '.csv';
        $users = User::with(['role', 'division', 'defaultSite']) // <—
            ->orderBy('name')
            ->get(['name', 'email', 'role_id', 'division_id', 'default_site_id']); // <—

        $headers = ['Content-Type' => 'text/csv'];
        return response()->streamDownload(function () use ($users) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Name', 'Email', 'Role', 'Division', 'Default Site']);
            foreach ($users as $u) {
                fputcsv($out, [
                    $u->name,
                    $u->email,
                    optional($u->role)->name,
                    optional($u->division)->name,
                    optional($u->defaultSite)->name, // <—
                ]);
            }
            fclose($out);
        }, $filename, $headers);
    }
}
