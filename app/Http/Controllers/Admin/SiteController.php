<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
        return view('admin.sites.form', ['site' => new Site()]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required','alpha_dash','max:30','unique:sites,code'],
            'name' => ['required','string' ,'integer', 'max:255'],
        ]);

        Site::create($data);

        return redirect()->route('admin.sites.index')
            ->with('success', 'Site berhasil dibuat.');
    }

    public function edit(Site $site)
    {
        return view('admin.sites.form', compact('site'));
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
        // site_configs akan ikut terhapus (cascade), users.default_site_id -> nullOnDelete
        $site->delete();

        return redirect()->route('admin.sites.index')
            ->with('success', 'Site berhasil dihapus.');
    }

    /**
     * Dipakai oleh form "Site Switcher" di sidenav:
     * POST route('admin.site.switch') dengan field "site".
     */
    public function switch(Request $request)
    {
        $request->validate([
            'site' => ['required','uuid','exists:sites,id'],
        ]);

        session(['site_id' => $request->string('site')->toString()]);

        return back()->with('success', 'Site aktif telah diubah.');
    }
}
