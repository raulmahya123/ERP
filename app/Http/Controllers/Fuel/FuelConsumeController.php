<?php

namespace App\Http\Controllers\Fuel;

use App\Http\Controllers\Controller;
use App\Models\Fuel\FuelConsume;
use App\Models\Fuel\FuelTank;
use App\Models\Fuel\FuelFlowMeter;
use App\Models\Asset;
use App\Models\User;
use App\Models\Site;
use Illuminate\Http\Request;

class FuelConsumeController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(FuelConsume::class, 'consume');
    }

    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $items = FuelConsume::with(['site', 'tank', 'unit', 'operator'])
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->when($request->filled('from'), fn($q) => $q->where('consume_at', '>=', $request->from))
            ->when($request->filled('to'), fn($q) => $q->where('consume_at', '<=', $request->to))
            ->when($request->filled('tank_id'), fn($q) => $q->where('tank_id', $request->tank_id))
            ->when($request->filled('unit_id'), fn($q) => $q->where('unit_id', $request->unit_id))
            ->when($request->filled('fuel_type'), fn($q) => $q->where('fuel_type', $request->fuel_type))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->orderByDesc('consume_at')
            ->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $tanks = FuelTank::when($siteId, fn($q) => $q->where('site_id', $siteId))->orderBy('code')->get(['id', 'code', 'name']);
        $units = Asset::when($siteId, fn($q) => $q->where('site_id', $siteId))->orderBy('code')->get(['id', 'code', 'name']);
        $operators = User::orderBy('name')->get(['id', 'name']);
        return view('admin.fuel.consumes.index', compact('items', 'sites', 'tanks', 'units', 'operators', 'siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $tanks = FuelTank::when($siteId, fn($q) => $q->where('site_id', $siteId))->orderBy('code')->get(['id', 'code', 'name']);
        $flowMeters = FuelFlowMeter::when($siteId, fn($q) => $q->where('site_id', $siteId))->orderBy('code')->get(['id', 'code', 'name']);
        $units = Asset::when($siteId, fn($q) => $q->where('site_id', $siteId))->orderBy('code')->get(['id', 'code', 'name']);
        $operators = User::orderBy('name')->get(['id', 'name']);
        $consume = new FuelConsume(['site_id' => $siteId, 'consume_at' => now(), 'fuel_type' => 'diesel', 'status' => 'draft']);
        return view('admin.fuel.consumes.create', compact('consume', 'sites', 'tanks', 'flowMeters', 'units', 'operators', 'siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'required|uuid|exists:sites,id',
            'tank_id' => 'nullable|uuid|exists:fuel_tanks,id',
            'flow_meter_id' => 'nullable|uuid|exists:fuel_flow_meters,id',
            'unit_id' => 'nullable|uuid|exists:assets,id',
            'operator_id' => 'nullable|uuid|exists:users,id',
            'consume_at' => 'required|date',
            'volume' => 'required|numeric|min:0.01',
            'fuel_type' => 'required|string|max:50',
            'meter_start' => 'nullable|numeric|min:0',
            'meter_end' => 'nullable|numeric|min:0',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'status' => 'required|string|max:20',
        ]);

        $data['created_by'] = $request->user()->id;
        $consume = FuelConsume::create($data);

        return redirect()->route('fuel.consumes.index', ['site' => $consume->site_id])
            ->with('success', 'Fuel Consume created.');
    }

    public function edit(FuelConsume $consume)
    {
        $siteId = $consume->site_id;
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $tanks = FuelTank::when($siteId, fn($q) => $q->where('site_id', $siteId))->orderBy('code')->get(['id', 'code', 'name']);
        $flowMeters = FuelFlowMeter::when($siteId, fn($q) => $q->where('site_id', $siteId))->orderBy('code')->get(['id', 'code', 'name']);
        $units = Asset::when($siteId, fn($q) => $q->where('site_id', $siteId))->orderBy('code')->get(['id', 'code', 'name']);
        $operators = User::orderBy('name')->get(['id', 'name']);
        return view('admin.fuel.consumes.edit', compact('consume', 'sites', 'tanks', 'flowMeters', 'units', 'operators', 'siteId'));
    }

    public function update(Request $request, FuelConsume $consume)
    {
        $data = $request->validate([
            'site_id' => 'required|uuid|exists:sites,id',
            'tank_id' => 'nullable|uuid|exists:fuel_tanks,id',
            'flow_meter_id' => 'nullable|uuid|exists:fuel_flow_meters,id',
            'unit_id' => 'nullable|uuid|exists:assets,id',
            'operator_id' => 'nullable|uuid|exists:users,id',
            'consume_at' => 'required|date',
            'volume' => 'required|numeric|min:0.01',
            'fuel_type' => 'required|string|max:50',
            'meter_start' => 'nullable|numeric|min:0',
            'meter_end' => 'nullable|numeric|min:0',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'status' => 'required|string|max:20',
        ]);

        $consume->update($data);
        return redirect()->route('fuel.consumes.index', ['site' => $consume->site_id])
            ->with('success', 'Fuel Consume updated.');
    }

    public function destroy(FuelConsume $consume)
    {
        $siteId = $consume->site_id;
        $consume->delete();
        return redirect()->route('fuel.consumes.index', ['site' => $siteId])
            ->with('success', 'Fuel Consume deleted.');
    }
}
