<?php

namespace App\Http\Controllers\Admin\Hse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hse\StorePicaRequest;
use App\Http\Requests\Hse\UpdatePicaRequest;
use App\Models\Incident;
use App\Models\HazardReport;
use App\Models\Pica;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PicaController extends Controller
{
    public function __construct()
    {
        // Resource abilities -> PicaPolicy (index/viewAny, show/view, create, store, edit/update, destroy)
        $this->authorizeResource(Pica::class, 'pica');

        // Aksi kustom (lihat PicaPolicy: markEffective / markIneffective / close)
        $this->middleware('can:markEffective,pica')->only('markEffective');
        $this->middleware('can:markIneffective,pica')->only('markIneffective');
        $this->middleware('can:close,pica')->only('close');
    }

    /**
     * GET /picas
     */
    // App\Http\Controllers\Admin\Hse\PicaController.php

    public function index(Request $request): View
    {
        $siteId = $this->currentSiteId();
        $q      = trim((string) $request->query('q', ''));
        $status = $request->query('status');

        $items = Pica::query()
            ->with(['incident:id,code,site_id', 'hazard:id,code,site_id', 'owner:id,name'])
            ->when($siteId, function ($qq) use ($siteId) {
                $qq->where(function ($w) use ($siteId) {
                    $w->whereHas('incident', fn($i) => $i->where('site_id', $siteId))
                        ->orWhereHas('hazard',   fn($h) => $h->where('site_id', $siteId));
                });
            })
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('title', 'like', "%{$q}%")
                        ->orWhere('problem_statement', 'like', "%{$q}%")
                        ->orWhere('root_cause', 'like', "%{$q}%")
                        ->orWhere('preventive_action', 'like', "%{$q}%");
                });
            })
            ->when($status, fn($qq) => $qq->where('status', $status))
            // FULL ORM: urut berdasarkan ENUM order lalu due_date
            ->orderBy('status')      // mengikuti urutan ENUM di migration
            ->orderBy('due_date')
            ->paginate(20)
            ->withQueryString();

        return view('admin.hse.picas.index', compact('items', 'q', 'status'));
    }


    /**
     * GET /picas/create
     */
    public function create(): View
    {
        $siteId = $this->currentSiteId();

        $incidents = Incident::query()
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderByDesc('occurred_at')
            ->limit(50)
            ->get(['id', 'code', 'occurred_at', 'site_id']);

        $hazards = HazardReport::query()
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderByDesc('observed_at')
            ->limit(50)
            ->get(['id', 'code', 'observed_at', 'site_id']);

        $owners = User::query()
            ->when($siteId, fn($q) => method_exists(User::class, 'scopeInSite') ? $q->inSite($siteId) : $q)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        $pica = new Pica();

        return view('admin.hse.picas.create', compact('pica', 'incidents', 'hazards', 'owners'));
    }

    /**
     * POST /picas
     */
    public function store(StorePicaRequest $request): RedirectResponse
    {
        $pica = Pica::create($request->validated());

        return redirect()
            ->route('admin.hse.picas.edit', $pica)
            ->with('success', 'PICA created.');
    }

    /**
     * GET /picas/{pica}/edit
     */
    public function edit(Pica $pica): View
    {
        $siteId = $this->currentSiteId();

        $incidents = Incident::query()
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderByDesc('occurred_at')
            ->limit(50)
            ->get(['id', 'code', 'occurred_at', 'site_id']);

        $hazards = HazardReport::query()
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderByDesc('observed_at')
            ->limit(50)
            ->get(['id', 'code', 'observed_at', 'site_id']);

        $owners = User::query()
            ->when($siteId, fn($q) => method_exists(User::class, 'scopeInSite') ? $q->inSite($siteId) : $q)
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return view('admin.hse.picas.edit', compact('pica', 'incidents', 'hazards', 'owners'));
    }

    /**
     * PUT/PATCH /picas/{pica}
     */
    public function update(UpdatePicaRequest $request, Pica $pica): RedirectResponse
    {
        $pica->update($request->validated());

        return back()->with('success', 'PICA updated.');
    }

    /**
     * DELETE /picas/{pica}
     */
    public function destroy(Pica $pica): RedirectResponse
    {
        $pica->delete();

        return redirect()
            ->route('admin.hse.picas.index')
            ->with('success', 'PICA deleted.');
    }

    /**
     * POST /picas/{pica}/mark-effective
     */
    public function markEffective(Pica $pica): RedirectResponse
    {
        $pica->update([
            'status'    => 'effective',
            'closed_at' => now(),
        ]);

        return back()->with('success', 'PICA marked effective.');
    }

    /**
     * POST /picas/{pica}/mark-ineffective
     */
    public function markIneffective(Pica $pica): RedirectResponse
    {
        $pica->update(['status' => 'ineffective']);

        return back()->with('success', 'PICA marked ineffective.');
    }

    /**
     * POST /picas/{pica}/close
     */
    public function close(Pica $pica): RedirectResponse
    {
        $pica->update([
            'status'    => 'closed',
            'closed_at' => now(),
        ]);

        return back()->with('success', 'PICA closed.');
    }

    /* =========================
     | Helpers
     |=========================*/
    protected function currentSiteId(): ?string
    {
        return session('site_id');
    }
}
