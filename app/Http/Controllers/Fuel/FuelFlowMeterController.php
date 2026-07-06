<?php

namespace App\Http\Controllers\Fuel;

use App\Http\Controllers\Controller;
use App\Models\Fuel\FuelFlowMeter;
use App\Models\Fuel\FuelTank;
use App\Models\Site;
use Illuminate\Http\Request;

class FuelFlowMeterController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(FuelFlowMeter::class, 'flowMeter');
    }

    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $items = FuelFlowMeter::with(['site', 'tank'])
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderBy('code')
            ->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        return view('admin.fuel.flow-meters.index', compact('items', 'sites', 'siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $tanks = FuelTank::when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderBy('code')->get(['id', 'code', 'name']);
        $meter = new FuelFlowMeter(['site_id' => $siteId, 'uom' => 'liter', 'is_active' => true]);
        return view('admin.fuel.flow-meters.create', compact('meter', 'sites', 'tanks', 'siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'required|uuid|exists:sites,id',
            'code' => 'required|string|max:50|unique:fuel_flow_meters,code',
            'name' => 'required|string|max:200',
            'tank_id' => 'nullable|uuid|exists:fuel_tanks,id',
            'meter_reading' => 'required|numeric|min:0',
            'uom' => 'required|string|max:20',
            'location' => 'nullable|string|max:200',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data['created_by'] = $request->user()->id;
        $meter = FuelFlowMeter::create($data);

        return redirect()->route('fuel.flow-meters.index', ['site' => $meter->site_id])
            ->with('success', 'Flow Meter created.');
    }

    public function edit(FuelFlowMeter $flowMeter)
    {
        $siteId = $flowMeter->site_id;
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $tanks = FuelTank::when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderBy('code')->get(['id', 'code', 'name']);
        return view('admin.fuel.flow-meters.edit', compact('flowMeter', 'sites', 'tanks', 'siteId'));
    }

    public function update(Request $request, FuelFlowMeter $flowMeter)
    {
        $data = $request->validate([
            'site_id' => 'required|uuid|exists:sites,id',
            'code' => 'required|string|max:50|unique:fuel_flow_meters,code,' . $flowMeter->id,
            'name' => 'required|string|max:200',
            'tank_id' => 'nullable|uuid|exists:fuel_tanks,id',
            'meter_reading' => 'required|numeric|min:0',
            'uom' => 'required|string|max:20',
            'location' => 'nullable|string|max:200',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $flowMeter->update($data);
        return redirect()->route('fuel.flow-meters.index', ['site' => $flowMeter->site_id])
            ->with('success', 'Flow Meter updated.');
    }

    public function destroy(FuelFlowMeter $flowMeter)
    {
        $siteId = $flowMeter->site_id;
        $flowMeter->delete();
        return redirect()->route('fuel.flow-meters.index', ['site' => $siteId])
            ->with('success', 'Flow Meter deleted.');
    }
}
