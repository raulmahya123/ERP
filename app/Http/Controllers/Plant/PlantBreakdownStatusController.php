<?php

namespace App\Http\Controllers\Plant;

use App\Http\Controllers\Controller;
use App\Models\Plant\PlantBreakdownStatus;
use App\Models\Asset;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;

class PlantBreakdownStatusController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(PlantBreakdownStatus::class, 'breakdownStatus');
    }

    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $q = PlantBreakdownStatus::query()
            ->with(['site', 'asset', 'reportedBy'])
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($request->filled('status'), fn($qq) => $qq->where('status', $request->status))
            ->when($request->filled('breakdown_code'), fn($qq) => $qq->where('breakdown_code', 'like', '%'.$request->breakdown_code.'%'))
            ->when($request->filled('asset_id'), fn($qq) => $qq->where('asset_id', $request->asset_id))
            ->when($request->filled('from'), fn($qq) => $qq->where('breakdown_start','>=',$request->from))
            ->when($request->filled('to'), fn($qq) => $qq->where('breakdown_start','<=',$request->to))
            ->orderByDesc('breakdown_start');

        $items = $q->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $assets = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))->orderBy('code')->get(['id','code','name']);
        $users = User::orderBy('name')->get(['id','name']);
        $statuses = ['open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'];

        return view('admin.plant.plant-breakdown-statuses.index', compact('items','sites','assets','users','statuses','siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $assets = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))->orderBy('code')->get(['id','code','name']);
        $statuses = ['open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'];

        $plantBreakdownStatus = new PlantBreakdownStatus([
            'site_id'        => $siteId,
            'breakdown_start' => now(),
            'status'          => 'open',
        ]);

        return view('admin.plant.plant-breakdown-statuses.create', compact('plantBreakdownStatus','sites','assets','statuses','siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id'        => 'required|uuid|exists:sites,id',
            'asset_id'       => 'required|uuid|exists:assets,id',
            'breakdown_start' => 'required|date',
            'breakdown_end'  => 'nullable|date|after_or_equal:breakdown_start',
            'breakdown_code' => 'nullable|string|max:50',
            'description'    => 'nullable|string',
            'status'         => 'required|string|max:50',
            'root_cause'     => 'nullable|string',
            'action_taken'   => 'nullable|string',
        ]);

        $data['reported_by'] = $request->user()->id;

        PlantBreakdownStatus::create($data);

        return redirect()
            ->route('plant.plant_breakdown_statuses.index', ['site' => $data['site_id']])
            ->with('success', 'Breakdown Status tersimpan.');
    }

    public function edit(Request $request, PlantBreakdownStatus $plantBreakdownStatus)
    {
        $siteId = $plantBreakdownStatus->site_id ?: ($request->query('site') ?: session('site_id'));

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $assets = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))->orderBy('code')->get(['id','code','name']);
        $statuses = ['open' => 'Open', 'in_progress' => 'In Progress', 'resolved' => 'Resolved', 'closed' => 'Closed'];

        return view('admin.plant.plant-breakdown-statuses.edit', compact('plantBreakdownStatus','sites','assets','statuses','siteId'));
    }

    public function update(Request $request, PlantBreakdownStatus $plantBreakdownStatus)
    {
        $data = $request->validate([
            'site_id'        => 'required|uuid|exists:sites,id',
            'asset_id'       => 'required|uuid|exists:assets,id',
            'breakdown_start' => 'required|date',
            'breakdown_end'  => 'nullable|date|after_or_equal:breakdown_start',
            'breakdown_code' => 'nullable|string|max:50',
            'description'    => 'nullable|string',
            'status'         => 'required|string|max:50',
            'root_cause'     => 'nullable|string',
            'action_taken'   => 'nullable|string',
        ]);

        $plantBreakdownStatus->update($data);

        return redirect()
            ->route('plant.plant_breakdown_statuses.index', ['site' => $data['site_id']])
            ->with('success', 'Breakdown Status diperbarui.');
    }

    public function destroy(Request $request, PlantBreakdownStatus $plantBreakdownStatus)
    {
        $siteId = $plantBreakdownStatus->site_id;
        $plantBreakdownStatus->delete();

        return redirect()
            ->route('plant.plant_breakdown_statuses.index', ['site' => $siteId])
            ->with('success', 'Breakdown Status dihapus.');
    }
}
