<?php

namespace App\Http\Controllers\Admin\Hse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hse\StoreHazardReportRequest;
use App\Http\Requests\Hse\UpdateHazardReportRequest;
use App\Models\HazardReport;
use App\Models\User;
use App\Models\Incident;
use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HazardReportController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\HazardReport::class, 'hazard');

        $this->middleware('can:assign,hazard')->only('assign');
        $this->middleware('can:mitigate,hazard')->only('mitigate');
        $this->middleware('can:verify,hazard')->only('verify');
        $this->middleware('can:close,hazard')->only('close');
    }

    public function index(Request $request)
    {
        $siteId  = $this->currentSiteId();
        $q       = trim((string) $request->query('q', ''));
        $status  = $request->query('status');           // reported|assigned|mitigated|verified|closed
        $from    = $request->query('from');             // yyyy-mm-dd
        $to      = $request->query('to');               // yyyy-mm-dd
        $sevMin  = $request->query('sev_min');          // 1..5 (severity_initial minimal)
        $sevMax  = $request->query('sev_max');          // 1..5 (severity_initial maksimal)

        $items = HazardReport::query()
            ->with(['site:id,name,code', 'reporter:id,name', 'assignee:id,name'])
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('code', 'like', "%{$q}%")
                      ->orWhere('category', 'like', "%{$q}%")
                      ->orWhere('location', 'like', "%{$q}%")
                      ->orWhere('description', 'like', "%{$q}%");
                });
            })
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->when($from, fn($qq) => $qq->whereDate('observed_at', '>=', $from))
            ->when($to,   fn($qq) => $qq->whereDate('observed_at', '<=', $to))
            ->when($sevMin, fn($qq) => $qq->where('severity_initial', '>=', (int)$sevMin))
            ->when($sevMax, fn($qq) => $qq->where('severity_initial', '<=', (int)$sevMax))
            ->orderByDesc('observed_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.hse.hazards.index', compact('items', 'q', 'status', 'from', 'to', 'sevMin', 'sevMax'));
    }

    public function create()
    {
        $siteId = $this->currentSiteId();

        $reporters = User::query()
            ->when($siteId, fn($q) => method_exists(User::class, 'scopeInSite') ? $q->inSite($siteId) : $q)
            ->orderBy('name')->get(['id','name','email']);

        $assignees = $reporters; // bisa dibatasi role tertentu

        $incidents = Incident::query()
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderByDesc('occurred_at')
            ->limit(50)
            ->get(['id','code','occurred_at']);

        $hazard = new HazardReport();

        return view('admin.hse.hazards.create', compact('hazard','reporters','assignees','incidents'));
    }

    public function store(StoreHazardReportRequest $request)
    {
        $data = $request->validated();

        // Default yang sering kosong dari form
        $data['site_id']     = $data['site_id']     ?? $this->currentSiteId();
        $data['observed_at'] = $data['observed_at'] ?? now();
        $data['status']      = $data['status']      ?? 'reported';
        $data['code']        = $data['code']        ?? $this->generateCode('HZR', $data['site_id']);

        // Hitung risk_initial / residual jika L & S ada
        if (isset($data['likelihood_initial'], $data['severity_initial'])) {
            $li = (int) $data['likelihood_initial'];
            $si = (int) $data['severity_initial'];
            $data['risk_initial'] = $li > 0 && $si > 0 ? $li * $si : null;
        }
        if (isset($data['likelihood_residual'], $data['severity_residual'])) {
            $lr = (int) $data['likelihood_residual'];
            $sr = (int) $data['severity_residual'];
            $data['risk_residual'] = $lr > 0 && $sr > 0 ? $lr * $sr : null;
        }

        $model = HazardReport::create($data);

        return redirect()->route('admin.hse.hazards.edit', $model)
            ->with('success', 'Hazard created.');
    }

    public function edit(HazardReport $hazard)
    {
        $siteId = $this->currentSiteId();

        $reporters = User::query()
            ->when($siteId, fn($q) => method_exists(User::class, 'scopeInSite') ? $q->inSite($siteId) : $q)
            ->orderBy('name')->get(['id','name','email']);

        $assignees = $reporters;

        $incidents = Incident::query()
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderByDesc('occurred_at')
            ->limit(50)
            ->get(['id','code','occurred_at']);

        return view('admin.hse.hazards.edit', compact('hazard','reporters','assignees','incidents'));
    }

    public function update(UpdateHazardReportRequest $request, HazardReport $hazard)
    {
        $data = $request->validated();

        // Jangan kosongkan nilai penting bila tidak dikirim
        $data['site_id']     = $data['site_id']     ?? $hazard->site_id ?? $this->currentSiteId();
        $data['observed_at'] = $data['observed_at'] ?? $hazard->observed_at ?? now();
        $data['status']      = $data['status']      ?? $hazard->status ?? 'reported';
        $data['code']        = $data['code']        ?? $hazard->code ?? $this->generateCode('HZR', $data['site_id']);

        // Recalculate risks bila L/S berubah
        if (array_key_exists('likelihood_initial', $data) || array_key_exists('severity_initial', $data)) {
            $li = (int) ($data['likelihood_initial'] ?? $hazard->likelihood_initial ?? 0);
            $si = (int) ($data['severity_initial']   ?? $hazard->severity_initial   ?? 0);
            $data['risk_initial'] = $li > 0 && $si > 0 ? $li * $si : null;
        }
        if (array_key_exists('likelihood_residual', $data) || array_key_exists('severity_residual', $data)) {
            $lr = (int) ($data['likelihood_residual'] ?? $hazard->likelihood_residual ?? 0);
            $sr = (int) ($data['severity_residual']   ?? $hazard->severity_residual   ?? 0);
            $data['risk_residual'] = $lr > 0 && $sr > 0 ? $lr * $sr : null;
        }

        $hazard->update($data);
        return back()->with('success', 'Hazard updated.');
    }

    public function destroy(HazardReport $hazard)
    {
        $hazard->delete();
        return redirect()->route('admin.hse.hazards.index')->with('success', 'Hazard deleted.');
    }

    /** Aksi workflow */
    public function assign(Request $request, HazardReport $hazard)
    {
        $data = $request->validate([
            'assignee_id' => ['required','uuid','exists:users,id'],
            'due_date'    => ['nullable','date'],
        ]);
        $hazard->update(array_merge($data, ['status' => 'assigned']));
        return back()->with('success', 'Hazard assigned.');
    }

    public function mitigate(HazardReport $hazard)
    {
        $hazard->update(['status' => 'mitigated']);
        return back()->with('success', 'Hazard mitigated.');
    }

    public function verify(Request $request, HazardReport $hazard)
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

    public function close(HazardReport $hazard)
    {
        $hazard->update(['status' => 'closed']);
        return back()->with('success', 'Hazard closed.');
    }

    /** Helpers */
    protected function currentSiteId(): ?string
    {
        return session('site_id');
    }

    /**
     * Generator kode: HZR-{SITECODE}-{YYYYMMDD}-{RANDOM}
     */
    protected function generateCode(string $prefix, ?string $siteId = null): string
    {
        $siteCode = 'GEN';
        if ($siteId) {
            $siteCode = strtoupper((string) (Site::query()->whereKey($siteId)->value('code') ?? 'GEN'));
        }
        return sprintf('%s-%s-%s-%s',
            $prefix,
            $siteCode,
            now()->format('Ymd'),
            Str::upper(Str::random(6))
        );
    }
}
