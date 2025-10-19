<?php

namespace App\Http\Controllers\Admin\Hse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hse\StoreHazardReportRequest;
use App\Http\Requests\Hse\UpdateHazardReportRequest;
use App\Models\HazardReport;
use App\Models\User;
use App\Models\Incident;
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
        $siteId = $this->currentSiteId();
        $q      = trim((string) $request->query('q', ''));
        $status = $request->query('status');

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
            ->orderByDesc('observed_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.hse.hazards.index', compact('items', 'q', 'status'));
    }

    public function create()
    {
        $siteId = $this->currentSiteId();

        $reporters = User::query()
            ->when($siteId, fn($q) => method_exists(User::class, 'scopeInSite') ? $q->inSite($siteId) : $q)
            ->orderBy('name')->get(['id','name','email']);

        $assignees = $reporters; // sementara sama; bisa difilter role tertentu kalau perlu

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
        $data['site_id'] = $data['site_id'] ?? $this->currentSiteId();
        $data['code']    = $data['code'] ?? $this->generateCode('HZR');

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
        $hazard->update($request->validated());
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
            'verified_by'       => ['required','uuid','exists:users,id'],
            'verification_note' => ['nullable','string'],
        ]);
        $hazard->update(array_merge($data, [
            'verified_at' => now(),
            'status'      => 'verified'
        ]));
        return back()->with('success', 'Hazard verified.');
    }

    public function close(HazardReport $hazard)
    {
        $hazard->update(['status' => 'closed']);
        return back()->with('success', 'Hazard closed.');
    }

    /** Helpers */
    protected function currentSiteId(): ?string { return session('site_id'); }

    protected function generateCode(string $prefix): string
    {
        return sprintf('%s-%s-%s', $prefix, now()->format('Ymd'), Str::upper(Str::random(6)));
    }
}
