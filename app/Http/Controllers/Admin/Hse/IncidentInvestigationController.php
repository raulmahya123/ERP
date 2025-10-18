<?php

namespace App\Http\Controllers\Admin\Hse;

use App\Http\Controllers\Controller;
use App\Http\Requests\Hse\StoreInvestigationRequest;
use App\Http\Requests\Hse\UpdateInvestigationRequest;
use App\Models\IncidentInvestigation;
use Illuminate\Http\Request;

class IncidentInvestigationController extends Controller
{
    public function index(Request $request)
    {
        $q    = trim((string) $request->query('q', ''));
        $stat = $request->query('status');

        $items = IncidentInvestigation::query()
            ->with(['incident:id,code,occurred_at', 'leadInvestigator:id,name'])
            ->when($q !== '', function ($qq) use ($q) {
                $qq->where('method', 'like', "%{$q}%")
                   ->orWhereHas('incident', fn($i) => $i->where('code', 'like', "%{$q}%"));
            })
            ->when($stat, fn($qq) => $qq->where('status', $stat))
            ->orderByDesc('started_at')
            ->paginate(20);

        return view('admin.hse.investigations.index', compact('items', 'q', 'stat'));
    }

    public function create()
    {
        $investigation = new IncidentInvestigation();
        return view('admin.hse.investigations.create', compact('investigation'));
    }

    public function store(StoreInvestigationRequest $request)
    {
        $model = IncidentInvestigation::create($request->validated());
        return redirect()->route('admin.hse.investigations.edit', $model)
            ->with('success', 'Investigation created.');
    }

    public function edit(IncidentInvestigation $investigation)
    {
        return view('admin.hse.investigations.edit', compact('investigation'));
    }

    public function update(UpdateInvestigationRequest $request, IncidentInvestigation $investigation)
    {
        $investigation->update($request->validated());
        return back()->with('success', 'Investigation updated.');
    }

    public function destroy(IncidentInvestigation $investigation)
    {
        $investigation->delete();
        return redirect()->route('admin.hse.investigations.index')->with('success', 'Investigation deleted.');
    }

    /** Aksi opsional */
    public function complete(IncidentInvestigation $investigation)
    {
        $investigation->update(['status' => 'closed', 'completed_at' => now()]);
        return back()->with('success', 'Investigation closed.');
    }

    public function reopen(IncidentInvestigation $investigation)
    {
        $investigation->update(['status' => 'open', 'completed_at' => null]);
        return back()->with('success', 'Investigation reopened.');
    }
}
