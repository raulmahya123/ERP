<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Scm\StorePitRequest;
use App\Http\Requests\Scm\UpdatePitRequest;
use App\Models\Scm\Pit;
use Illuminate\Http\Request;

class PitController extends Controller
{
    public function index(Request $r)
    {
        $siteId = (string) session('site_id');

        $pits = Pit::where('site_id', $siteId)
            ->when($r->filled('q'), function ($w) use ($r) {
                $term = trim($r->q);
                $w->where(function ($qq) use ($term) {
                    $qq->where('code', 'like', "%{$term}%")
                       ->orWhere('name', 'like', "%{$term}%");
                });
            })
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        return view('admin.scm.pits.index', compact('pits'));
    }

    public function create()
    {
        return view('admin.scm.pits.create');
    }

    public function store(StorePitRequest $r)
    {
        $data   = $r->validated();
        $siteId = (string) session('site_id');

        $pit = new Pit();
        $pit->site_id = $siteId;
        $pit->code    = $data['code'];
        $pit->name    = $data['name'] ?? null;
        $pit->active  = (bool) ($data['active'] ?? false);
        $pit->extra   = isset($data['extra']) && $data['extra'] !== ''
            ? json_decode($data['extra'], true)
            : null;
        $pit->save();

        return redirect()->route('scm.pits.index')->with('success', 'Pit berhasil dibuat.');
    }

    public function edit(Pit $pit)
    {
        $this->authorizeSite($pit->site_id);
        return view('admin.scm.pits.edit', compact('pit'));
    }

    public function update(UpdatePitRequest $r, Pit $pit)
    {
        $this->authorizeSite($pit->site_id);

        $data = $r->validated();

        $pit->code   = $data['code'];
        $pit->name   = $data['name'] ?? null;
        $pit->active = (bool) ($data['active'] ?? false);
        $pit->extra  = isset($data['extra']) && $data['extra'] !== ''
            ? json_decode($data['extra'], true)
            : null;
        $pit->save();

        return redirect()->route('scm.pits.index')->with('success', 'Pit berhasil diupdate.');
    }

    public function destroy(Pit $pit)
    {
        $this->authorizeSite($pit->site_id);
        $pit->delete();

        return back()->with('success', 'Pit dihapus.');
    }

    private function authorizeSite(string $modelSiteId): void
    {
        $siteId = (string) session('site_id');
        abort_unless($modelSiteId === $siteId, 403, 'Site context tidak cocok.');
    }
}
