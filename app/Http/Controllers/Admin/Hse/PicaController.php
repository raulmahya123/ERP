<?php

declare(strict_types=1);

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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

final class PicaController extends Controller
{
    /** Batasi nilai yang diizinkan untuk status (atau pakai Enum). */
    private const STATUS = ['open','effective','ineffective','closed'];

    /** Batas minimum/maximum pagination. */
    private const MIN_PER_PAGE = 5;
    private const MAX_PER_PAGE = 100;
    private const DEFAULT_PER_PAGE = 20;

    public function __construct()
    {
        $this->authorizeResource(Pica::class, 'pica');
        $this->middleware('can:markEffective,pica')->only('markEffective');
        $this->middleware('can:markIneffective,pica')->only('markIneffective');
        $this->middleware('can:close,pica')->only('close');
    }

    /** GET /picas */
    public function index(Request $request): View
    {
        $siteId  = $this->currentSiteId();
        $q       = $this->sanitizeSearch((string) $request->query('q', ''));
        $status  = $this->sanitizeStatus($request->query('status'));
        $perPage = $this->sanitizePerPage((string) $request->query('per_page', (string) self::DEFAULT_PER_PAGE));

        $items = Pica::query()
            ->select([
                'id','code','reference','title','status',
                'due_date','owner_id','related_incident_id','related_hazard_id',
                'problem_statement','root_cause','preventive_action','closed_at',
                'created_at','updated_at',
            ])
            ->with([
                'incident:id,code,site_id',
                'hazard:id,code,site_id',
                'owner:id,name,email',
            ])
            // Filter site via relasi incident/hazard (hemat query dengan exists)
            ->when($siteId, function ($qq) use ($siteId) {
                $qq->where(function ($w) use ($siteId) {
                    $w->whereHas('incident', fn($i) => $i->where('site_id', $siteId))
                      ->orWhereHas('hazard',   fn($h) => $h->where('site_id', $siteId));
                });
            })
            // Pencarian aman (dibersihkan & dibind), LIKE binding handled oleh builder
            ->when($q !== '', function ($qq) use ($q) {
                $like = "%{$q}%";
                $qq->where(function ($w) use ($like) {
                    $w->where('code', 'like', $like)
                      ->orWhere('reference', 'like', $like)
                      ->orWhere('title', 'like', $like)
                      ->orWhere('problem_statement', 'like', $like)
                      ->orWhere('root_cause', 'like', $like)
                      ->orWhere('preventive_action', 'like', $like)
                      ->orWhereHas('incident', fn($i) => $i->where('code', 'like', $like))
                      ->orWhereHas('hazard',   fn($h) => $h->where('code', 'like', $like));
                });
            })
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->orderBy('status')   // enum ordering
            ->orderBy('due_date')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.hse.picas.index', compact('items', 'q', 'status'));
    }

    /** GET /picas/create */
    public function create(): View
    {
        $siteId = $this->currentSiteId();

        $incidents = Incident::query()
            ->select(['id','code','occurred_at','site_id'])
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->latest('occurred_at')->limit(50)->get();

        $hazards = HazardReport::query()
            ->select(['id','code','observed_at','site_id'])
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->latest('observed_at')->limit(50)->get();

        $owners = User::query()
            ->select(['id','name','email'])
            ->when($siteId, fn($q) => method_exists(User::class, 'scopeInSite') ? $q->inSite($siteId) : $q)
            ->orderBy('name')
            ->get();

        $pica = new Pica();

        return view('admin.hse.picas.create', compact('pica','incidents','hazards','owners'));
    }

    /** POST /picas */
    public function store(StorePicaRequest $request): RedirectResponse
    {
        $data = $request->validated();

        // Normalisasi status
        $data['status'] = $this->sanitizeStatus($data['status'] ?? 'open') ?? 'open';

        // Determine reference & site code
        [$reference, $siteCode] = $this->resolveReferenceAndSiteCode(
            $data['related_incident_id'] ?? null,
            $data['related_hazard_id'] ?? null
        );

        $data['reference'] = $data['reference'] ?? $reference;
        $data['code']      = $data['code']      ?? $this->makePicaCode('PCA', $siteCode);

        if (empty($data['owner_id']) && auth()->check()) {
            $data['owner_id'] = (int) auth()->id();
        }

        DB::transaction(fn() => Pica::create($data));

        return redirect()
            ->route('admin.hse.picas.index', $this->indexQueryParams($request))
            ->with('success', 'PICA created.');
    }

    /** GET /picas/{pica}/edit */
    public function edit(Pica $pica): View
    {
        $siteId = $this->currentSiteId();

        $incidents = Incident::query()
            ->select(['id','code','occurred_at','site_id'])
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->latest('occurred_at')->limit(50)->get();

        $hazards = HazardReport::query()
            ->select(['id','code','observed_at','site_id'])
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->latest('observed_at')->limit(50)->get();

        $owners = User::query()
            ->select(['id','name','email'])
            ->when($siteId, fn($q) => method_exists(User::class, 'scopeInSite') ? $q->inSite($siteId) : $q)
            ->orderBy('name')->get();

        // Pastikan relasi disiapkan (menghindari N+1 saat blade)
        $pica->loadMissing([
            'incident:id,code,site_id',
            'hazard:id,code,site_id',
            'owner:id,name,email',
        ]);

        return view('admin.hse.picas.edit', compact('pica','incidents','hazards','owners'));
    }

    /** PUT/PATCH /picas/{pica} */
    public function update(UpdatePicaRequest $request, Pica $pica): RedirectResponse
    {
        $data = $request->validated();

        $data['status']   = $this->sanitizeStatus($data['status'] ?? $pica->status ?? 'open') ?? 'open';
        $data['owner_id'] = $data['owner_id'] ?? $pica->owner_id ?? (auth()->id() ?: null);

        $relatedIncidentId = $data['related_incident_id'] ?? $pica->related_incident_id;
        $relatedHazardId   = $data['related_hazard_id'] ?? $pica->related_hazard_id;

        // Refresh reference / code bila relasi berubah atau reference kosong
        if (
            empty($data['reference']) ||
            $relatedIncidentId !== $pica->related_incident_id ||
            $relatedHazardId   !== $pica->related_hazard_id
        ) {
            [$reference, $siteCode] = $this->resolveReferenceAndSiteCode($relatedIncidentId, $relatedHazardId);
            $data['reference'] = $data['reference'] ?? $reference;

            if (empty($pica->code) && empty($data['code'])) {
                $data['code'] = $this->makePicaCode('PCA', $siteCode);
            }
        }

        if (empty($data['code'])) {
            [$ref, $sc]   = $this->resolveReferenceAndSiteCode($relatedIncidentId, $relatedHazardId);
            $data['code'] = $pica->code ?? $this->makePicaCode('PCA', $sc);
        }

        DB::transaction(fn() => $pica->update($data));

        return redirect()
            ->route('admin.hse.picas.index', $this->indexQueryParams($request))
            ->with('success', 'PICA updated.');
    }

    /** DELETE /picas/{pica} */
    public function destroy(Request $request, Pica $pica): RedirectResponse
    {
        DB::transaction(fn() => $pica->delete());

        return redirect()
            ->route('admin.hse.picas.index', $this->indexQueryParams($request))
            ->with('success', 'PICA deleted.');
    }

    /** POST /picas/{pica}/mark-effective */
    public function markEffective(Request $request, Pica $pica): RedirectResponse
    {
        DB::transaction(function () use ($pica) {
            $pica->update([
                'status'    => 'effective',
                'closed_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.hse.picas.index', $this->indexQueryParams($request))
            ->with('success', 'PICA marked effective.');
    }

    /** POST /picas/{pica}/mark-ineffective */
    public function markIneffective(Request $request, Pica $pica): RedirectResponse
    {
        DB::transaction(fn() => $pica->update(['status' => 'ineffective']));

        return redirect()
            ->route('admin.hse.picas.index', $this->indexQueryParams($request))
            ->with('success', 'PICA marked ineffective.');
    }

    /** POST /picas/{pica}/close */
    public function close(Request $request, Pica $pica): RedirectResponse
    {
        DB::transaction(function () use ($pica) {
            $pica->update([
                'status'    => 'closed',
                'closed_at' => now(),
            ]);
        });

        return redirect()
            ->route('admin.hse.picas.index', $this->indexQueryParams($request))
            ->with('success', 'PICA closed.');
    }

    /* =========================
     | Helpers
     |=========================*/
    protected function currentSiteId(): ?string
    {
        // Bisa di-override via middleware/site-context
        return session('site_id');
    }

    /** Amankan & rapikan string pencarian (anti XSS, whitespace) */
    protected function sanitizeSearch(string $raw): string
    {
        $s = strip_tags($raw);
        $s = preg_replace('/[^\p{L}\p{N}\s\-\_\.\#]/u', '', $s) ?? '';
        return trim(preg_replace('/\s+/', ' ', $s) ?? '');
    }

    /** Whitelist status */
    protected function sanitizeStatus(?string $status): ?string
    {
        if (!$status) return null;
        $s = strtolower(trim($status));
        return in_array($s, self::STATUS, true) ? $s : null;
    }

    /** Per-page aman (dibatasi range) */
    protected function sanitizePerPage(string $perPage): int
    {
        $v = (int) filter_var($perPage, FILTER_VALIDATE_INT) ?: self::DEFAULT_PER_PAGE;
        return max(self::MIN_PER_PAGE, min(self::MAX_PER_PAGE, $v));
    }

    /** Bawa kembali filter/paging aktif saat kembali ke index */
    protected function indexQueryParams(Request $request): array
    {
        $params = $request->only(['q','status','owner_id','per_page','page']);
        return array_filter($params, fn($v) => !is_null($v) && $v !== '');
    }

    /**
     * Kembalikan [reference, siteCode] dari incident/hazard terpilih.
     * - Prioritas incident jika keduanya diisi.
     */
    protected function resolveReferenceAndSiteCode(?string $incidentId, ?string $hazardId): array
    {
        if ($incidentId) {
            $inc = Incident::query()->select('code','site_id')->find($incidentId);
            if ($inc) {
                $siteCode = $this->getSiteCode($inc->site_id);
                return [$inc->code, $siteCode];
            }
        }
        if ($hazardId) {
            $hz = HazardReport::query()->select('code','site_id')->find($hazardId);
            if ($hz) {
                $siteCode = $this->getSiteCode($hz->site_id);
                return [$hz->code, $siteCode];
            }
        }
        return [null, 'GEN'];
    }

    protected function getSiteCode(?string $siteId): string
    {
        if (!$siteId) return 'GEN';
        return strtoupper((string) (\App\Models\Site::query()->whereKey($siteId)->value('code') ?? 'GEN'));
    }

    protected function makePicaCode(string $prefix, ?string $siteCode = 'GEN'): string
    {
        $siteCode = $siteCode ? strtoupper($siteCode) : 'GEN';
        return sprintf(
            '%s-%s-%s-%s',
            $prefix,
            $siteCode,
            now()->format('Ymd'),
            Str::upper(Str::random(6))
        );
    }
}
