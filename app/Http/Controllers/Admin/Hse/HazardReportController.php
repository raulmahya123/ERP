<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Hse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hse\StoreHazardReportRequest;
use App\Http\Requests\Hse\UpdateHazardReportRequest;
use App\Models\HazardReport;
use App\Models\Incident;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

final class HazardReportController extends Controller
{
    /** Whitelist status agar aman untuk filter & workflow */
    private const STATUSES = ['reported','assigned','mitigated','verified','closed'];

    public function __construct()
    {
        $this->authorizeResource(HazardReport::class, 'hazard');

        // Aksi workflow khusus (cek Policy)
        $this->middleware('can:assign,hazard')->only('assign');
        $this->middleware('can:mitigate,hazard')->only('mitigate');
        $this->middleware('can:verify,hazard')->only('verify');
        $this->middleware('can:close,hazard')->only('close');
    }

    /** GET /hse/hazards */
    public function index(Request $request): View
    {
        $siteId  = $this->currentSiteId();
        $q       = $this->sanitizeSearch((string) $request->query('q', ''));
        $status  = $request->filled('status') && in_array($request->query('status'), self::STATUSES, true)
            ? (string) $request->query('status')
            : null;

        // Tanggal (boleh kosong)
        $from = $this->tryParseDate($request->query('from'));
        $to   = $this->tryParseDate($request->query('to'));

        // Rentang severity 1..5 (opsional)
        $sevMin  = $this->clampIntNullable($request->query('sev_min'), 1, 5);
        $sevMax  = $this->clampIntNullable($request->query('sev_max'), 1, 5);

        // Per page aman (5..100)
        $perPage = $this->clampInt((int) $request->integer('per_page', 20), 5, 100);

        $items = HazardReport::query()
            ->select([
                'id','code','site_id','reporter_id','assignee_id',
                'observed_at','location','category','status',
                'likelihood_initial','severity_initial','risk_initial',
                'created_at',
            ])
            ->with([
                'site:id,name,code',
                'reporter:id,name',
                'assignee:id,name',
            ])
            ->when($siteId, fn ($q2) => $q2->where('site_id', $siteId))
            ->when($q !== '', function ($q2) use ($q) {
                $like = "%{$q}%";
                $q2->where(function ($w) use ($like) {
                    $w->where('code', 'like', $like)
                      ->orWhere('category', 'like', $like)
                      ->orWhere('location', 'like', $like)
                      ->orWhere('description', 'like', $like);
                });
            })
            ->when($status, fn ($q2) => $q2->where('status', $status))
            ->when($from, fn ($q2) => $q2->where('observed_at', '>=', $from->copy()->startOfDay()))
            ->when($to,   fn ($q2) => $q2->where('observed_at', '<=', $to->copy()->endOfDay()))
            ->when($sevMin !== null, fn ($q2) => $q2->where('severity_initial', '>=', $sevMin))
            ->when($sevMax !== null, fn ($q2) => $q2->where('severity_initial', '<=', $sevMax))
            ->orderByDesc('observed_at')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.hse.hazards.index', [
            'items'  => $items,
            'q'      => $q,
            'status' => $status,
            'from'   => $from?->toDateTimeLocalString() ?: null,
            'to'     => $to?->toDateTimeLocalString() ?: null,
            'sevMin' => $sevMin,
            'sevMax' => $sevMax,
        ]);
    }

    /** GET /hse/hazards/create */
    public function create(): View
    {
        $siteId = $this->currentSiteId();

        $reporters = User::query()
            ->when($siteId, fn ($q) => method_exists(User::class, 'scopeInSite') ? $q->inSite($siteId) : $q)
            ->orderBy('name')
            ->get(['id','name','email']);

        $assignees = $reporters; // bisa dibatasi role tertentu

        $incidents = Incident::query()
            ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
            ->orderByDesc('occurred_at')
            ->limit(50)
            ->get(['id','code','occurred_at','site_id']);

        $hazard = new HazardReport([
            'status'      => 'reported',
            'observed_at' => now(),
        ]);

        return view('admin.hse.hazards.create', compact('hazard','reporters','assignees','incidents'));
    }

    /** POST /hse/hazards */
    public function store(StoreHazardReportRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $data['site_id']     = $data['site_id']     ?? $this->currentSiteId();
        $data['observed_at'] = $data['observed_at'] ?? now();
        $data['status']      = in_array($data['status'] ?? '', self::STATUSES, true) ? $data['status'] : 'reported';

        // Code unik (retry singkat menghindari collision)
        $data['code'] = $data['code'] ?? $this->generateUniqueHazardCode($data['site_id']);

        // Hitung risk jika L & S tersedia
        $data['risk_initial']  = $this->calculateRisk($data['likelihood_initial'] ?? null, $data['severity_initial'] ?? null);
        $data['risk_residual'] = $this->calculateRisk($data['likelihood_residual'] ?? null, $data['severity_residual'] ?? null);

        HazardReport::create($data);

        // Balik ke index
        return redirect()
            ->route('admin.hse.hazards.index')
            ->with('success', 'Hazard created.');
    }

    /** GET /hse/hazards/{hazard}/edit */
    public function edit(HazardReport $hazard): View
    {
        $siteId = $this->currentSiteId();

        $reporters = User::query()
            ->when($siteId, fn ($q) => method_exists(User::class, 'scopeInSite') ? $q->inSite($siteId) : $q)
            ->orderBy('name')
            ->get(['id','name','email']);

        $assignees = $reporters;

        $incidents = Incident::query()
            ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
            ->orderByDesc('occurred_at')
            ->limit(50)
            ->get(['id','code','occurred_at','site_id']);

        return view('admin.hse.hazards.edit', compact('hazard','reporters','assignees','incidents'));
    }

    /** PUT /hse/hazards/{hazard} */
    public function update(UpdateHazardReportRequest $request, HazardReport $hazard): RedirectResponse
    {
        $data = $request->validated();

        // Jangan kosongkan nilai penting
        $data['site_id']     = $data['site_id']     ?? $hazard->site_id ?? $this->currentSiteId();
        $data['observed_at'] = $data['observed_at'] ?? $hazard->observed_at ?? now();
        $data['status']      = isset($data['status']) && in_array($data['status'], self::STATUSES, true)
            ? $data['status']
            : ($hazard->status ?? 'reported');

        // Pastikan code tetap ada
        if (empty($data['code'])) {
            $data['code'] = $hazard->code ?? $this->generateUniqueHazardCode($data['site_id']);
        }

        // Recalculate risks bila input L/S disediakan
        if (array_key_exists('likelihood_initial', $data) || array_key_exists('severity_initial', $data)) {
            $li = (int) ($data['likelihood_initial'] ?? $hazard->likelihood_initial ?? 0);
            $si = (int) ($data['severity_initial']   ?? $hazard->severity_initial   ?? 0);
            $data['risk_initial'] = $this->calculateRisk($li, $si);
        }
        if (array_key_exists('likelihood_residual', $data) || array_key_exists('severity_residual', $data)) {
            $lr = (int) ($data['likelihood_residual'] ?? $hazard->likelihood_residual ?? 0);
            $sr = (int) ($data['severity_residual']   ?? $hazard->severity_residual   ?? 0);
            $data['risk_residual'] = $this->calculateRisk($lr, $sr);
        }

        $hazard->update($data);

        // Balik ke index
        return redirect()
            ->route('admin.hse.hazards.index')
            ->with('success', 'Hazard updated.');
    }

    /** DELETE /hse/hazards/{hazard} */
    public function destroy(HazardReport $hazard): RedirectResponse
    {
        $hazard->delete();

        return redirect()
            ->route('admin.hse.hazards.index')
            ->with('success', 'Hazard deleted.');
    }

    /** POST /hse/hazards/{hazard}/assign */
    public function assign(Request $request, HazardReport $hazard): RedirectResponse
    {
        $data = $request->validate([
            'assignee_id' => ['required','uuid','exists:users,id'],
            'due_date'    => ['nullable','date'],
        ]);

        $hazard->update([
            'assignee_id' => $data['assignee_id'],
            'due_date'    => $data['due_date'] ?? null,
            'status'      => 'assigned',
        ]);

        return back()->with('success', 'Hazard assigned.');
    }

    /** POST /hse/hazards/{hazard}/mitigate */
    public function mitigate(HazardReport $hazard): RedirectResponse
    {
        $hazard->update(['status' => 'mitigated']);

        return back()->with('success', 'Hazard mitigated.');
    }

    /** POST /hse/hazards/{hazard}/verify */
    public function verify(Request $request, HazardReport $hazard): RedirectResponse
    {
        $data = $request->validate([
            'verified_by'       => ['nullable','uuid','exists:users,id'],
            'verification_note' => ['nullable','string'],
        ]);

        $hazard->update([
            'verified_by'       => $data['verified_by'] ?? (auth()->id() ?: $hazard->verified_by),
            'verification_note' => $data['verification_note'] ?? $hazard->verification_note,
            'verified_at'       => now(),
            'status'            => 'verified',
        ]);

        return back()->with('success', 'Hazard verified.');
    }

    /** POST /hse/hazards/{hazard}/close */
    public function close(HazardReport $hazard): RedirectResponse
    {
        $hazard->update(['status' => 'closed']);

        return back()->with('success', 'Hazard closed.');
    }

    /* ================= Helpers ================ */

    protected function currentSiteId(): ?string
    {
        return session('site_id');
    }

    private function sanitizeSearch(string $q): string
    {
        $q = trim(mb_substr($q, 0, 60));
        // huruf/angka/spasi/- . _ saja → aman untuk LIKE
        return trim((string) preg_replace('/[^\p{L}\p{N}\s\-\._]/u', '', $q));
    }

    private function tryParseDate(?string $raw): ?Carbon
    {
        if (!$raw) return null;
        try {
            return Carbon::parse($raw);
        } catch (\Throwable) {
            return null;
        }
    }

    private function clampInt(int $v, int $min, int $max): int
    {
        return max($min, min($v, $max));
    }

    private function clampIntNullable($v, int $min, int $max): ?int
    {
        if ($v === null || $v === '') return null;
        $int = (int) $v;
        if ($int < $min || $int > $max) return null;
        return $int;
    }

    private function calculateRisk($likelihood, $severity): ?int
    {
        $l = (int) $likelihood;
        $s = (int) $severity;
        return ($l > 0 && $s > 0) ? ($l * $s) : null;
    }

    /**
     * Generator kode unik: HZR-{SITECODE}-{YYYYMMDD}-{RANDOM}
     * Dengan retry kecil untuk menghindari collision race condition.
     */
    private function generateUniqueHazardCode(?string $siteId): string
    {
        $siteCode = 'GEN';
        if ($siteId) {
            $code = Site::query()->whereKey($siteId)->value('code');
            if (is_string($code) && $code !== '') {
                $siteCode = strtoupper($code);
            }
        }

        for ($i = 0; $i < 3; $i++) {
            $code = sprintf(
                'HZR-%s-%s-%s',
                $siteCode,
                now()->format('Ymd'),
                Str::upper(Str::random(6))
            );

            if (!HazardReport::query()->where('code', $code)->exists()) {
                return $code;
            }
        }

        // fallback (sangat kecil kemungkinan tercapai)
        return sprintf('HZR-%s-%s-%s', $siteCode, now()->format('Ymd'), Str::uuid()->toString());
    }
}
