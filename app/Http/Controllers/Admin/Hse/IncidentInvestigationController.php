<?php

namespace App\Http\Controllers\Admin\Hse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hse\StoreInvestigationRequest;
use App\Http\Requests\Hse\UpdateInvestigationRequest;
use App\Models\Incident;
use App\Models\IncidentInvestigation;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;

class IncidentInvestigationController extends Controller
{
    public function __construct()
    {
        // Policy resource
        $this->authorizeResource(IncidentInvestigation::class, 'investigation');

        // Aksi kustom
        $this->middleware('can:complete,investigation')->only('complete');
        $this->middleware('can:reopen,investigation')->only('reopen');
    }

    /**
     * List investigations (filter q, status, site, date range).
     */
    public function index(Request $request)
    {
        $siteId = $this->currentSiteId();
        $q      = trim((string) $request->query('q', ''));
        $stat   = $request->query('status'); // open|review|closed
        $from   = $request->query('from');   // yyyy-mm-dd
        $to     = $request->query('to');     // yyyy-mm-dd

        $items = IncidentInvestigation::query()
            ->with([
                'incident:id,code,occurred_at,site_id',
                'leadInvestigator:id,name,email',
            ])
            ->when($siteId, fn ($qq) =>
                $qq->whereHas('incident', fn ($i) => $i->where('site_id', $siteId))
            )
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('code', 'like', "%{$q}%")              // <— cari di kode investigasi sendiri
                      ->orWhere('method', 'like', "%{$q}%")
                      ->orWhereHas('incident', fn ($i) => $i->where('code', 'like', "%{$q}%"));
                });
            })
            ->when($stat, fn ($qq) => $qq->where('status', $stat))
            ->when($from, fn ($qq) => $qq->whereDate('started_at', '>=', $from))
            ->when($to,   fn ($qq) => $qq->whereDate('started_at', '<=', $to))
            ->orderByDesc('started_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.hse.investigations.index', compact('items', 'q', 'stat', 'from', 'to'));
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
        $data = $request->validated();

        // Default penting
        $data['status']     = $data['status']     ?? 'open';
        $data['started_at'] = $data['started_at'] ?? now();

        // Generate code pakai site code dari incident (jika ada)
        $siteCode = $this->getSiteCodeFromIncident($data['incident_id'] ?? null);
        $data['code'] = $data['code'] ?? $this->generateCode('INV', $siteCode);

        $model = IncidentInvestigation::create($data);

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
        $data = $request->validated();

        // Pastikan nilai inti tidak jadi kosong
        $data['status']     = $data['status']     ?? $investigation->status ?? 'open';
        $data['started_at'] = $data['started_at'] ?? $investigation->started_at ?? now();
        $data['incident_id']= $data['incident_id'] ?? $investigation->incident_id;

        // Isi code kalau belum ada / kosong
        if (empty($data['code'])) {
            $siteCode   = $this->getSiteCodeFromIncident($data['incident_id'] ?? null);
            $data['code'] = $investigation->code ?? $this->generateCode('INV', $siteCode);
        }

        $investigation->update($data);

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

    /**
     * Ambil site code dari incident (untuk generator kode).
     */
    protected function getSiteCodeFromIncident(?string $incidentId): ?string
    {
        if (!$incidentId) return null;
        $incident = Incident::query()->select('site_id')->find($incidentId);
        if (!$incident || !$incident->site_id) return null;

        return strtoupper((string) (Site::query()->whereKey($incident->site_id)->value('code') ?? 'GEN'));
    }

    /**
     * Generator kode: INV-{SITECODE}-{YYYYMMDD}-{RANDOM}
     */
    protected function generateCode(string $prefix, ?string $siteCode = null): string
    {
        $siteCode = $siteCode ? strtoupper($siteCode) : 'GEN';
        return sprintf('%s-%s-%s-%s',
            $prefix,
            $siteCode,
            now()->format('Ymd'),
            Str::upper(Str::random(6))
        );
    }
}
