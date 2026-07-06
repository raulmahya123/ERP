<?php

namespace App\Http\Controllers\Fuel;

use App\Http\Controllers\Controller;
use App\Models\Fuel\FuelTank;
use App\Models\Site;
use Illuminate\Http\Request;

class FuelTankController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(FuelTank::class, 'tank');
    }

    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $items = FuelTank::with('site')
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->when($request->filled('fuel_type'), fn($q) => $q->where('fuel_type', $request->fuel_type))
            ->orderBy('code')
            ->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        return view('admin.fuel.tanks.index', compact('items', 'sites', 'siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $tank = new FuelTank(['site_id' => $siteId, 'fuel_type' => 'diesel', 'is_active' => true]);
        return view('admin.fuel.tanks.create', compact('tank', 'sites', 'siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'required|uuid|exists:sites,id',
            'code' => 'required|string|max:50|unique:fuel_tanks,code',
            'name' => 'required|string|max:200',
            'fuel_type' => 'required|string|max:50',
            'capacity' => 'required|numeric|min:0',
            'current_volume' => 'required|numeric|min:0',
            'location' => 'nullable|string|max:200',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $data['created_by'] = $request->user()->id;
        $tank = FuelTank::create($data);

        return redirect()->route('fuel.tanks.index', ['site' => $tank->site_id])
            ->with('success', 'Fuel Tank created.');
    }

    public function edit(FuelTank $tank)
    {
        $siteId = $tank->site_id;
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        return view('admin.fuel.tanks.edit', compact('tank', 'sites', 'siteId'));
    }

    public function update(Request $request, FuelTank $tank)
    {
        $data = $request->validate([
            'site_id' => 'required|uuid|exists:sites,id',
            'code' => 'required|string|max:50|unique:fuel_tanks,code,' . $tank->id,
            'name' => 'required|string|max:200',
            'fuel_type' => 'required|string|max:50',
            'capacity' => 'required|numeric|min:0',
            'current_volume' => 'required|numeric|min:0',
            'location' => 'nullable|string|max:200',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $tank->update($data);
        return redirect()->route('fuel.tanks.index', ['site' => $tank->site_id])
            ->with('success', 'Fuel Tank updated.');
    }

    public function destroy(FuelTank $tank)
    {
        $siteId = $tank->site_id;
        $tank->delete();
        return redirect()->route('fuel.tanks.index', ['site' => $siteId])
            ->with('success', 'Fuel Tank deleted.');
    }
}
