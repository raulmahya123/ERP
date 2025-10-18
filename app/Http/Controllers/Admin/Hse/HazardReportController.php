<?php

namespace App\Http\Controllers\Admin\Hse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hse\StoreHazardReportRequest;
use App\Http\Requests\Hse\UpdateHazardReportRequest;
use App\Models\HazardReport;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class HazardReportController extends Controller
{
    public function index(Request $request)
    {
        $siteId = $this->currentSiteId();
        $q      = trim((string) $request->query('q', ''));
        $status = $request->query('status');

        $items = HazardReport::query()
            ->with(['site:id,name,code', 'reporter:id,name', 'assignee:id,name'])
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where('code', 'like', "%{$q}%")
                   ->orWhere('category', 'like', "%{$q}%")
                   ->orWhere('location', 'like', "%{$q}%")
                   ->orWhere('description', 'like', "%{$q}%");
            })
            ->when($status, fn($qq) => $qq->where('status', $status))
            ->orderByDesc('observed_at')
            ->paginate(20);

        return view('admin.hse.hazards.index', compact('items', 'q', 'status'));
    }

    public function create()
    {
        $hazard = new HazardReport();
        return view('admin.hse.hazards.create', compact('hazard'));
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
        return view('admin.hse.hazards.edit', compact('hazard'));
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

    /** Aksi opsional */
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
            'verified_by'      => ['required','uuid','exists:users,id'],
            'verification_note'=> ['nullable','string'],
        ]);
        $hazard->update(array_merge($data, ['verified_at' => now(), 'status' => 'verified']));
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
