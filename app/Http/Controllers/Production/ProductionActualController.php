<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\ProductionActual;
use App\Models\Site;
use Illuminate\Http\Request;

class ProductionActualController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ProductionActual::class, 'actual');
    }


    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $items = ProductionActual::query()
            ->with('site')
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderByDesc('actual_date')
            ->orderBy('shift')
            ->paginate(15)
            ->withQueryString();

        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        return view('admin.production.production-actuals.index', compact('items', 'sites', 'siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        $actual = new ProductionActual([
            'site_id' => $siteId,
            'actual_date' => now(),
        ]);

        return view('admin.production.production-actuals.create', compact('actual', 'sites', 'siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'shift_plan_id' => 'nullable|exists:production_shift_plans,id',
            'actual_date' => 'required|date',
            'shift' => 'nullable|string|max:20',
            'volume' => 'nullable|numeric',
            'ob_volume' => 'nullable|numeric',
            'waste_volume' => 'nullable|numeric',
            'overburden_volume' => 'nullable|numeric',
            'uom' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'recorded_by' => 'nullable|exists:users,id',
        ]);

        ProductionActual::create($data);

        return redirect()
            ->route('production.production-actuals.index', ['site' => $data['site_id']])
            ->with('success', 'Production Actual tersimpan.');
    }

    public function edit(Request $request, ProductionActual $productionActual)
    {
        $siteId = $productionActual->site_id ?: ($request->query('site') ?: session('site_id'));
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        return view('admin.production.production-actuals.edit', compact('productionActual', 'sites', 'siteId'));
    }

    public function update(Request $request, ProductionActual $productionActual)
    {
        $data = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'shift_plan_id' => 'nullable|exists:production_shift_plans,id',
            'actual_date' => 'required|date',
            'shift' => 'nullable|string|max:20',
            'volume' => 'nullable|numeric',
            'ob_volume' => 'nullable|numeric',
            'waste_volume' => 'nullable|numeric',
            'overburden_volume' => 'nullable|numeric',
            'uom' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'recorded_by' => 'nullable|exists:users,id',
        ]);

        $productionActual->update($data);

        return redirect()
            ->route('production.production-actuals.index', ['site' => $data['site_id']])
            ->with('success', 'Production Actual diperbarui.');
    }

    public function destroy(Request $request, ProductionActual $productionActual)
    {
        $siteId = $productionActual->site_id;
        $productionActual->delete();

        return redirect()
            ->route('production.production-actuals.index', ['site' => $siteId])
            ->with('success', 'Production Actual dihapus.');
    }
}
