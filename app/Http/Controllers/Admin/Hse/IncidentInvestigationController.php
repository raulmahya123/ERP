<?php

namespace App\Http\Controllers\Admin\Hse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hse\StoreInvestigationRequest;
use App\Http\Requests\Hse\UpdateInvestigationRequest;
use App\Models\Incident;
use App\Models\IncidentInvestigation;
use App\Models\User;
use Illuminate\Http\Request;

class IncidentInvestigationController extends Controller
{
    public function __construct()
    {
        // Pakai policy IncidentInvestigation (resource-actions: index, show, create, store, edit, update, destroy)
        $this->authorizeResource(IncidentInvestigation::class, 'investigation');

        // Aksi kustom (lihat policy -> complete(), reopen())
        $this->middleware('can:complete,investigation')->only('complete');
        $this->middleware('can:reopen,investigation')->only('reopen');
    }

    /**
     * List investigations (filter q, status, site).
     */
    public function index(Request $request)
    {
        $siteId = $this->currentSiteId();
        $q      = trim((string) $request->query('q', ''));
        $stat   = $request->query('status');

        $items = IncidentInvestigation::query()
            ->with(['incident:id,code,occurred_at,site_id', 'leadInvestigator:id,name,email'])
            ->when($siteId, fn ($qq) =>
                $qq->whereHas('incident', fn ($i) => $i->where('site_id', $siteId))
            )
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('method', 'like', "%{$q}%")
                      ->orWhereHas('incident', fn ($i) => $i->where('code', 'like', "%{$q}%"));
                });
            })
            ->when($stat, fn ($qq) => $qq->where('status', $stat))
            ->orderByDesc('started_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.hse.investigations.index', compact('items', 'q', 'stat'));
    }

    /**
     * Show create form (drop-down incident & investigator).
     */
    public function create()
    {
        $siteId = $this->currentSiteId();

        $incidents = Incident::query()
            ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
            ->orderByDesc('occurred_at')
            ->limit(50)
            ->get(['id','code','occurred_at']);

        $investigators = User::query()
            ->when($siteId, fn ($q) => method_exists(User::class, 'scopeInSite') ? $q->inSite($siteId) : $q)
            ->orderBy('name')
            ->get(['id','name','email']);

        $investigation = new IncidentInvestigation();

        return view('admin.hse.investigations.create', compact('investigation','incidents','investigators'));
    }

    /**
     * Store new investigation.
     */
    public function store(StoreInvestigationRequest $request)
    {
        $model = IncidentInvestigation::create($request->validated());

        return redirect()
            ->route('admin.hse.investigations.edit', $model)
            ->with('success', 'Investigation created.');
    }

    /**
     * Edit form.
     */
    public function edit(IncidentInvestigation $investigation)
    {
        $siteId = $this->currentSiteId();

        $incidents = Incident::query()
            ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
            ->orderByDesc('occurred_at')
            ->limit(50)
            ->get(['id','code','occurred_at']);

        $investigators = User::query()
            ->when($siteId, fn ($q) => method_exists(User::class, 'scopeInSite') ? $q->inSite($siteId) : $q)
            ->orderBy('name')
            ->get(['id','name','email']);

        return view('admin.hse.investigations.edit', compact('investigation','incidents','investigators'));
    }

    /**
     * Update investigation.
     */
    public function update(UpdateInvestigationRequest $request, IncidentInvestigation $investigation)
    {
        $investigation->update($request->validated());

        return back()->with('success', 'Investigation updated.');
    }

    /**
     * Soft delete.
     */
    public function destroy(IncidentInvestigation $investigation)
    {
        $investigation->delete();

        return redirect()
            ->route('admin.hse.investigations.index')
            ->with('success', 'Investigation deleted.');
    }

    /**
     * Mark investigation completed.
     */
    public function complete(IncidentInvestigation $investigation)
    {
        $investigation->update([
            'status'       => 'closed',
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Investigation closed.');
    }

    /**
     * Reopen investigation.
     */
    public function reopen(IncidentInvestigation $investigation)
    {
        $investigation->update([
            'status'       => 'open',
            'completed_at' => null,
        ]);

        return back()->with('success', 'Investigation reopened.');
    }

    /* =========================
     | Helpers
     |=========================*/
    protected function currentSiteId(): ?string
    {
        return session('site_id');
    }
}
