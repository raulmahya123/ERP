<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\ProductionMonthlyPlan;
use App\Models\Site;
use Illuminate\Http\Request;

class ProductionMonthlyPlanController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ProductionMonthlyPlan::class, 'monthlyPlan');
    }


    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $items = ProductionMonthlyPlan::query()
            ->with('site')
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->paginate(15)
            ->withQueryString();

        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        return view('admin.production.production-monthly-plans.index', compact('items', 'sites', 'siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        $monthlyPlan = new ProductionMonthlyPlan([
            'site_id' => $siteId,
            'status' => 'draft',
        ]);

        return view('admin.production.production-monthly-plans.create', compact('monthlyPlan', 'sites', 'siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'plan_number' => 'required|string|max:50',
            'year' => 'required|integer|min:2000|max:2099',
            'month' => 'required|integer|min:1|max:12',
            'target_volume' => 'nullable|numeric',
            'uom' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|max:50',
            'created_by' => 'nullable|exists:users,id',
        ]);

        ProductionMonthlyPlan::create($data);

        return redirect()
            ->route('production.production-monthly-plans.index', ['site' => $data['site_id']])
            ->with('success', 'Monthly Plan tersimpan.');
    }

    public function edit(Request $request, ProductionMonthlyPlan $productionMonthlyPlan)
    {
        $siteId = $productionMonthlyPlan->site_id ?: ($request->query('site') ?: session('site_id'));
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        return view('admin.production.production-monthly-plans.edit', compact('productionMonthlyPlan', 'sites', 'siteId'));
    }

    public function update(Request $request, ProductionMonthlyPlan $productionMonthlyPlan)
    {
        $data = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'plan_number' => 'required|string|max:50',
            'year' => 'required|integer|min:2000|max:2099',
            'month' => 'required|integer|min:1|max:12',
            'target_volume' => 'nullable|numeric',
            'uom' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'status' => 'nullable|string|max:50',
            'created_by' => 'nullable|exists:users,id',
        ]);

        $productionMonthlyPlan->update($data);

        return redirect()
            ->route('production.production-monthly-plans.index', ['site' => $data['site_id']])
            ->with('success', 'Monthly Plan diperbarui.');
    }

    public function destroy(Request $request, ProductionMonthlyPlan $productionMonthlyPlan)
    {
        $siteId = $productionMonthlyPlan->site_id;
        $productionMonthlyPlan->delete();

        return redirect()
            ->route('production.production-monthly-plans.index', ['site' => $siteId])
            ->with('success', 'Monthly Plan dihapus.');
    }
}
