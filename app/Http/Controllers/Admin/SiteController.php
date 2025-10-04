<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Str;

class SiteController extends Controller
{
    /**
     * Daftar semua site
     */
    public function index(Request $request)
    {
        $sites = Site::query()
            ->when($request->filled('q'), function ($q) use ($request) {
                $q->where('code', 'like', '%'.$request->q.'%')
                  ->orWhere('name', 'like', '%'.$request->q.'%');
            })
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        return view('admin.sites.index', compact('sites'));
    }

    /**
     * Form create
     */
    public function create()
    {
        return view('admin.sites.form', [
            'site' => new Site()
        ]);
    }

    /**
     * Simpan site baru
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required','max:30','alpha_dash','unique:sites,code'],
            'name' => ['required','max:120'],
        ]);

        Site::create([
            'id'   => (string) Str::uuid(),
            'code' => $data['code'],
            'name' => $data['name'],
        ]);

        return redirect()->route('admin.sites.index')->with('status','Site created.');
    }

    /**
     * Form edit
     */
    public function edit(Site $site)
    {
        return view('admin.sites.form', compact('site'));
    }

    /**
     * Update site
     */
    public function update(Request $request, Site $site)
    {
        $data = $request->validate([
            'code' => ['required','max:30','alpha_dash', Rule::unique('sites','code')->ignore($site->id)],
            'name' => ['required','max:120'],
        ]);

        $site->update($data);

        return redirect()->route('admin.sites.index')->with('status','Site updated.');
    }

    /**
     * Hapus site
     */
    public function destroy(Site $site)
    {
        $site->delete();

        return redirect()->route('admin.sites.index')->with('status','Site deleted.');
    }
}
