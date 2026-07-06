<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\ProductionShiftPlan;
use App\Models\Site;
use Illuminate\Http\Request;

class ProductionShiftPlanController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ProductionShiftPlan::class, 'shiftPlan');
    }


    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $items = ProductionShiftPlan::query()
            ->with('site')
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderByDesc('plan_date')
            ->orderBy('shift')
            ->paginate(15)
            ->withQueryString();

        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        return view('admin.production.production-shift-plans.index', compact('items', 'sites', 'siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        $shiftPlan = new ProductionShiftPlan([
            'site_id' => $siteId,
            'plan_date' => now(),
            'status' => 'draft',
        ]);

        return view('admin.production.production-shift-plans.create', compact('shiftPlan', 'sites', 'siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'monthly_plan_id' => 'nullable|exists:production_monthly_plans,id',
            'plan_date' => 'required|date',
            'shift' => 'nullable|string|max:20',
            'target_volume' => 'nullable|numeric',
            'target_ob' => 'nullable|numeric',
            'uom' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|max:50',
            'created_by' => 'nullable|exists:users,id',
        ]);

        ProductionShiftPlan::create($data);

        return redirect()
            ->route('production.production-shift-plans.index', ['site' => $data['site_id']])
            ->with('success', 'Shift Plan tersimpan.');
    }

    public function edit(Request $request, ProductionShiftPlan $productionShiftPlan)
    {
        $siteId = $productionShiftPlan->site_id ?: ($request->query('site') ?: session('site_id'));
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        return view('admin.production.production-shift-plans.edit', compact('productionShiftPlan', 'sites', 'siteId'));
    }

    public function update(Request $request, ProductionShiftPlan $productionShiftPlan)
    {
        $data = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'monthly_plan_id' => 'nullable|exists:production_monthly_plans,id',
            'plan_date' => 'required|date',
            'shift' => 'nullable|string|max:20',
            'target_volume' => 'nullable|numeric',
            'target_ob' => 'nullable|numeric',
            'uom' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|max:50',
            'created_by' => 'nullable|exists:users,id',
        ]);

        $productionShiftPlan->update($data);

        return redirect()
            ->route('production.production-shift-plans.index', ['site' => $data['site_id']])
            ->with('success', 'Shift Plan diperbarui.');
    }

    public function destroy(Request $request, ProductionShiftPlan $productionShiftPlan)
    {
        $siteId = $productionShiftPlan->site_id;
        $productionShiftPlan->delete();

        return redirect()
            ->route('production.production-shift-plans.index', ['site' => $siteId])
            ->with('success', 'Shift Plan dihapus.');
    }
}
