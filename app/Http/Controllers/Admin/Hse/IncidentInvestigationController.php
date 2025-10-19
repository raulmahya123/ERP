<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin\Hse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hse\StoreInvestigationRequest;
use App\Http\Requests\Hse\UpdateInvestigationRequest;
use App\Models\Incident;
use App\Models\IncidentInvestigation;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class IncidentInvestigationController extends Controller
{
    /** Whitelist status agar aman untuk filter/update */
    private const STATUSES = ['open', 'review', 'closed'];

    public function __construct()
    {
        // Policy resource (binding param harus 'investigation')
        $this->authorizeResource(IncidentInvestigation::class, 'investigation');

        // Aksi kustom
        $this->middleware('can:complete,investigation')->only('complete');
        $this->middleware('can:reopen,investigation')->only('reopen');
    }

    /** GET /hse/investigations */
    public function index(Request $request): View
    {
        $siteId = $this->currentSiteId();

        $qRaw = (string) $request->query('q', '');
        $q    = $this->sanitizeSearch($qRaw);

        $statusRaw = $request->query('status');
        $status    = is_string($statusRaw) && in_array($statusRaw, self::STATUSES, true) ? $statusRaw : null;

        $from = $this->tryParseDate($request->query('from')); // yyyy-mm-dd / datetime
        $to   = $this->tryParseDate($request->query('to'));

        // per_page clamp 5..100
        $perPage = (int) $request->integer('per_page', 20);
        $perPage = max(5, min($perPage, 100));

        $items = IncidentInvestigation::query()
            ->select([
                'id','code','incident_id','lead_investigator_id','method',
                'status','started_at','completed_at','created_at',
            ])
            ->with([
                'incident:id,code,occurred_at,site_id',
                'leadInvestigator:id,name,email',
            ])
            // Filter by current site via relasi incident
            ->when($siteId, fn ($qq) =>
                $qq->whereHas('incident', fn ($i) => $i->where('site_id', $siteId))
            )
            // Keyword aman untuk LIKE
            ->when($q !== '', function ($qq) use ($q) {
                $like = "%{$q}%";
                $qq->where(function ($w) use ($like) {
                    $w->where('code', 'like', $like)
                      ->orWhere('method', 'like', $like)
                      ->orWhereHas('incident', fn ($i) => $i->where('code', 'like', $like));
                });
            })
            // Status whitelist
            ->when($status, fn ($qq) => $qq->where('status', $status))
            // Rentang tanggal (pakai started_at)
            ->when($from, fn ($qq) => $qq->where('started_at', '>=', $from->copy()->startOfDay()))
            ->when($to,   fn ($qq) => $qq->where('started_at', '<=', $to->copy()->endOfDay()))
            ->orderByDesc('started_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.hse.investigations.index', [
            'items'  => $items,
            'q'      => $q,
            'stat'   => $status,
            'from'   => $from,
            'to'     => $to,
        ]);
    }

    /** GET /hse/investigations/create */
    public function create(): View
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

        $investigation = new IncidentInvestigation([
            'status'     => 'open',
            'started_at' => now(),
        ]);

        return view('admin.hse.investigations.create', compact('investigation','incidents','investigators'));
    }

    /** POST /hse/investigations */
    public function store(StoreInvestigationRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Default aman
        $incomingStatus  = $data['status'] ?? 'open';
        $data['status']  = in_array($incomingStatus, self::STATUSES, true) ? $incomingStatus : 'open';
        $data['started_at'] = $data['started_at'] ?? now();

        // Generate code pakai site code dari incident (jika valid)
        $siteCode  = $this->getSiteCodeFromIncident($data['incident_id'] ?? null);
        $data['code'] = $data['code'] ?? $this->generateCode('INV', $siteCode);

        $model = IncidentInvestigation::create($data);

        // Balik ke index + highlight baris baru
        return redirect()
            ->route('admin.hse.investigations.index')
            ->with('success', 'Investigation created.')
            ->with('highlight_id', $model->id);
    }

    /** GET /hse/investigations/{investigation}/edit */
    public function edit(IncidentInvestigation $investigation): View
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

        $investigation->loadMissing([
            'incident:id,code,occurred_at,site_id',
            'leadInvestigator:id,name,email',
        ]);

        return view('admin.hse.investigations.edit', compact('investigation','incidents','investigators'));
    }

    /** PUT/PATCH /hse/investigations/{investigation} */
    public function update(UpdateInvestigationRequest $request, IncidentInvestigation $investigation): RedirectResponse
    {
        $data = $request->validated();

        // Immutability: abaikan 'code' yang datang dari client
        unset($data['code']);

        // Pastikan field inti tidak null
        $data['status']      = isset($data['status']) && in_array($data['status'], self::STATUSES, true)
            ? $data['status']
            : ($investigation->status ?? 'open');

        $data['started_at']  = $data['started_at']  ?? ($investigation->started_at ?? now());
        $data['incident_id'] = $data['incident_id'] ?? $investigation->incident_id;

        // Update aman
        $investigation->update($data);

        return redirect()
            ->route('admin.hse.investigations.index')
            ->with('success', 'Investigation updated.')
            ->with('highlight_id', $investigation->id);
    }

    /** DELETE /hse/investigations/{investigation} */
    public function destroy(IncidentInvestigation $investigation): RedirectResponse
    {
        $investigation->delete();

        return redirect()
            ->route('admin.hse.investigations.index')
            ->with('success', 'Investigation deleted.');
    }

    /** PATCH /hse/investigations/{investigation}/complete */
    public function complete(IncidentInvestigation $investigation): RedirectResponse
    {
        $investigation->update([
            'status'       => 'closed',
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Investigation closed.');
    }

    /** PATCH /hse/investigations/{investigation}/reopen */
    public function reopen(IncidentInvestigation $investigation): RedirectResponse
    {
        $investigation->update([
            'status'       => 'open',
            'completed_at' => null,
        ]);

        return back()->with('success', 'Investigation reopened.');
    }

    /* ================= Helpers ================ */

    protected function currentSiteId(): ?string
    {
        return session('site_id');
    }

    /** Ambil site code dari incident (aman & tahan error) */
    protected function getSiteCodeFromIncident(?string $incidentId): ?string
    {
        if (!$incidentId || !Str::isUuid($incidentId)) {
            return null;
        }

        try {
            $siteId = Incident::query()->whereKey($incidentId)->value('site_id');
            if (!$siteId || !Str::isUuid((string) $siteId)) {
                return null;
            }
            $code = Site::query()->whereKey((string) $siteId)->value('code');
            return is_string($code) && $code !== '' ? strtoupper($code) : 'GEN';
        } catch (\Throwable) {
            return null;
        }
    }

    /** Generator kode: {PREFIX}-{SITECODE}-{YYYYMMDD}-{RAND} */
    protected function generateCode(string $prefix, ?string $siteCode = null): string
    {
        $sc = $siteCode ? strtoupper($siteCode) : 'GEN';
        return sprintf(
            '%s-%s-%s-%s',
            strtoupper($prefix),
            $sc,
            now()->format('Ymd'),
            Str::upper(Str::random(6))
        );
    }

    /** Sanitasi keyword untuk LIKE */
    private function sanitizeSearch(string $q): string
    {
        $q = trim($q);
        if ($q === '') return '';
        $q = mb_substr($q, 0, 60);
        // huruf/angka/spasi/- . _
        return trim(preg_replace('/[^\p{L}\p{N}\s\-\._]/u', '', $q) ?? '');
    }

    /** Parse tanggal aman (null kalau invalid) */
    private function tryParseDate(?string $date): ?Carbon
    {
        if (!$date) return null;
        try {
            return Carbon::parse($date);
        } catch (\Throwable) {
            return null;
        }
    }
}
