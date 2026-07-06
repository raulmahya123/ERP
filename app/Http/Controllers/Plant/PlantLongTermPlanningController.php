<?php

namespace App\Http\Controllers\Plant;

use App\Http\Controllers\Controller;
use App\Models\Plant\PlantLongTermPlanning;
use App\Models\Asset;
use App\Models\Site;
use Illuminate\Http\Request;

class PlantLongTermPlanningController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(PlantLongTermPlanning::class, 'planning');
    }

    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $q = PlantLongTermPlanning::query()
            ->with(['site', 'asset'])
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($request->filled('year'), fn($qq) => $qq->where('year', $request->year))
            ->when($request->filled('plan_type'), fn($qq) => $qq->where('plan_type', $request->plan_type))
            ->when($request->filled('status'), fn($qq) => $qq->where('status', $request->status))
            ->when($request->filled('asset_id'), fn($qq) => $qq->where('asset_id', $request->asset_id))
            ->orderByDesc('year')
            ->orderBy('plan_type');

        $items = $q->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $assets = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))->orderBy('code')->get(['id','code','name']);
        $planTypes = ['overhaul' => 'Overhaul', 'replacement' => 'Replacement', 'major_repair' => 'Major Repair', 'upgrade' => 'Upgrade', 'shutdown' => 'Shutdown'];
        $statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];

        return view('admin.plant.plant-long-term-plannings.index', compact('items','sites','assets','planTypes','statuses','siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $assets = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))->orderBy('code')->get(['id','code','name']);
        $planTypes = ['overhaul' => 'Overhaul', 'replacement' => 'Replacement', 'major_repair' => 'Major Repair', 'upgrade' => 'Upgrade', 'shutdown' => 'Shutdown'];
        $statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];

        $plantLongTermPlanning = new PlantLongTermPlanning([
            'site_id' => $siteId,
            'year'    => now()->year,
            'status'  => 'draft',
        ]);

        return view('admin.plant.plant-long-term-plannings.create', compact('plantLongTermPlanning','sites','assets','planTypes','statuses','siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id'       => 'required|uuid|exists:sites,id',
            'asset_id'      => 'required|uuid|exists:assets,id',
            'year'          => 'required|integer|min:2000|max:2100',
            'plan_type'     => 'required|string|max:50',
            'description'   => 'nullable|string',
            'planned_date'  => 'nullable|date',
            'estimated_cost' => 'nullable|numeric|min:0',
            'status'        => 'required|string|max:50',
        ]);

        PlantLongTermPlanning::create($data);

        return redirect()
            ->route('plant.plant_long_term_plannings.index', ['site' => $data['site_id']])
            ->with('success', 'Long Term Planning tersimpan.');
    }

    public function edit(Request $request, PlantLongTermPlanning $plantLongTermPlanning)
    {
        $siteId = $plantLongTermPlanning->site_id ?: ($request->query('site') ?: session('site_id'));

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $assets = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))->orderBy('code')->get(['id','code','name']);
        $planTypes = ['overhaul' => 'Overhaul', 'replacement' => 'Replacement', 'major_repair' => 'Major Repair', 'upgrade' => 'Upgrade', 'shutdown' => 'Shutdown'];
        $statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];

        return view('admin.plant.plant-long-term-plannings.edit', compact('plantLongTermPlanning','sites','assets','planTypes','statuses','siteId'));
    }

    public function update(Request $request, PlantLongTermPlanning $plantLongTermPlanning)
    {
        $data = $request->validate([
            'site_id'       => 'required|uuid|exists:sites,id',
            'asset_id'      => 'required|uuid|exists:assets,id',
            'year'          => 'required|integer|min:2000|max:2100',
            'plan_type'     => 'required|string|max:50',
            'description'   => 'nullable|string',
            'planned_date'  => 'nullable|date',
            'estimated_cost' => 'nullable|numeric|min:0',
            'status'        => 'required|string|max:50',
        ]);

        $plantLongTermPlanning->update($data);

        return redirect()
            ->route('plant.plant_long_term_plannings.index', ['site' => $data['site_id']])
            ->with('success', 'Long Term Planning diperbarui.');
    }

    public function destroy(Request $request, PlantLongTermPlanning $plantLongTermPlanning)
    {
        $siteId = $plantLongTermPlanning->site_id;
        $plantLongTermPlanning->delete();

        return redirect()
            ->route('plant.plant_long_term_plannings.index', ['site' => $siteId])
            ->with('success', 'Long Term Planning dihapus.');
    }
}
