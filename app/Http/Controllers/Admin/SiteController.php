<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;

class SiteController extends Controller
{
    public function index(Request $request)
    {
        $q = (string) $request->query('q', '');

        $sites = Site::query()
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('code', 'like', "%{$q}%")
                      ->orWhere('name', 'like', "%{$q}%");
                });
            })
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        return view('admin.sites.index', compact('sites', 'q'));
    }

    public function create()
    {
        // opsi dari DB untuk datalist
        $site  = new Site();
        $codes = Site::orderBy('code')->pluck('code')->unique()->values();
        $names = Site::orderBy('name')->pluck('name')->unique()->values();

        return view('admin.sites.form', compact('site','codes','names'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required','alpha_dash','max:30','unique:sites,code'],
            'name' => ['required','string','max:255'],
        ]);

        Site::create($data);

        return redirect()->route('admin.sites.index')
            ->with('success', 'Site berhasil dibuat.');
    }

    public function edit(Site $site)
    {
        $codes = Site::orderBy('code')->pluck('code')->unique()->values();
        $names = Site::orderBy('name')->pluck('name')->unique()->values();

        return view('admin.sites.form', compact('site','codes','names'));
    }

    public function update(Request $request, Site $site)
    {
        $data = $request->validate([
            'code' => ['required','alpha_dash','max:30', Rule::unique('sites','code')->ignore($site->id)],
            'name' => ['required','string','max:255'],
        ]);

        $site->update($data);

        return redirect()->route('admin.sites.index')
            ->with('success', 'Site berhasil diperbarui.');
    }

    public function destroy(Site $site)
    {
        try {
            // Pastikan FK sudah diset: master_records.site_id -> nullOnDelete,
            // site_configs.site_id -> RESTRICT/CASCADE sesuai desain kamu.
            $site->delete();

            // Jika site aktif di session, kosongkan
            if (session('site_id') === $site->id) {
                session()->forget('site_id');
            }

            return redirect()->route('admin.sites.index')
                ->with('success', 'Site berhasil dihapus.');
        } catch (QueryException $e) {
            // MySQL 1451 = cannot delete/update parent row (FK restrict)
            if ((int) ($e->errorInfo[1] ?? 0) === 1451) {
                return back()->withErrors([
                    'site' => 'Tidak bisa menghapus site karena masih dipakai data lain. '.
                              'Hapus/putuskan relasi terlebih dahulu (contoh: site_configs atau akses user).'
                ]);
            }
            throw $e;
        }
    }

    /**
     * Dipakai oleh form "Site Switcher" (opsional) atau endpoint admin cepat.
     * POST route('admin.site.switch') dengan field "site".
     *
     * Aturan:
     * - GM: boleh pilih semua site.
     * - Non-GM: jika tabel pivot 'user_sites' ada, hanya boleh pilih site yang terdaftar untuk user.
     *           jika pivot tidak ada, fallback: site harus exist.
     */
    public function switch(Request $request)
    {
        $data = $request->validate([
            'site' => ['required','uuid'],
        ]);

        $user = $request->user();

        // Deteksi apakah user GM
        $roleKey = strtolower(trim($user?->role?->key ?? $user?->role?->name ?? ''));
        $isGM = in_array($roleKey, ['gm','general manager','generalmanager'], true);

        // Cek allowed site
        if ($isGM) {
            $allowed = Site::where('id', $data['site'])->exists();
        } else {
            if (Schema::hasTable('user_sites')) {
                $allowed = DB::table('user_sites')
                    ->where('user_id', $user->id)
                    ->where('site_id', $data['site'])
                    ->exists();
            } else {
                $allowed = Site::where('id', $data['site'])->exists();
            }
        }

        abort_unless($allowed, 403, 'Anda tidak berhak memilih site ini.');

        session(['site_id' => (string) $data['site']]);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Site aktif telah diubah.', 'site_id' => $data['site']]);
        }

        return back()->with('success', 'Site aktif telah diubah.');
    }
}
