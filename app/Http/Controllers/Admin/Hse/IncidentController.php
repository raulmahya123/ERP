<?php

namespace App\Http\Controllers\Admin\Hse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hse\StoreIncidentRequest;
use App\Http\Requests\Hse\UpdateIncidentRequest;
use App\Models\Incident;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class IncidentController extends Controller
{
    public function index(Request $request)
    {
        $siteId = $this->currentSiteId();
        $q      = trim((string) $request->query('q', ''));
        $status = $request->query('status');
        $from   = $request->query('from');
        $to     = $request->query('to');

        $items = Incident::query()
            ->with(['site:id,name,code', 'reporter:id,name,email'])
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
            ->when($from, fn($qq) => $qq->where('occurred_at', '>=', $from))
            ->when($to, fn($qq) => $qq->where('occurred_at', '<=', $to))
            ->orderByDesc('occurred_at')
            ->paginate(20);

        return view('admin.hse.incidents.index', compact('items', 'q', 'status', 'from', 'to'));
    }

    public function create()
    {
        $incident = new Incident();
        return view('admin.hse.incidents.create', compact('incident'));
    }

    public function store(StoreIncidentRequest $request)
    {
        $data = $request->validated();
        $data['site_id'] = $data['site_id'] ?? $this->currentSiteId();
        $data['code']    = $data['code'] ?? $this->generateCode('INC');
        $incident = Incident::create($data);

        return redirect()->route('admin.hse.incidents.edit', $incident)
            ->with('success', 'Incident created.');
    }

    public function edit(Incident $incident)
    {
        return view('admin.hse.incidents.edit', compact('incident'));
    }

    public function update(UpdateIncidentRequest $request, Incident $incident)
    {
        $incident->update($request->validated());
        return back()->with('success', 'Incident updated.');
    }

    public function destroy(Incident $incident)
    {
        $incident->delete();
        return redirect()->route('admin.hse.incidents.index')->with('success', 'Incident deleted.');
    }

    /** Helpers */
    protected function currentSiteId(): ?string { return session('site_id'); }
    protected function generateCode(string $prefix): string
    {
        return sprintf('%s-%s-%s', $prefix, now()->format('Ymd'), Str::upper(Str::random(6)));
    }
}
