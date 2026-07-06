<?php

namespace App\Http\Controllers\Fuel;

use App\Http\Controllers\Controller;
use App\Models\Fuel\FuelReceive;
use App\Models\Fuel\FuelTank;
use App\Models\Site;
use Illuminate\Http\Request;

class FuelReceiveController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(FuelReceive::class, 'receive');
    }

    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $items = FuelReceive::with(['site', 'tank'])
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->when($request->filled('from'), fn($q) => $q->where('receive_at', '>=', $request->from))
            ->when($request->filled('to'), fn($q) => $q->where('receive_at', '<=', $request->to))
            ->when($request->filled('fuel_type'), fn($q) => $q->where('fuel_type', $request->fuel_type))
            ->orderByDesc('receive_at')
            ->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $tanks = FuelTank::when($siteId, fn($q) => $q->where('site_id', $siteId))->orderBy('code')->get(['id', 'code', 'name']);
        return view('admin.fuel.receives.index', compact('items', 'sites', 'tanks', 'siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $tanks = FuelTank::when($siteId, fn($q) => $q->where('site_id', $siteId))->orderBy('code')->get(['id', 'code', 'name']);
        $receive = new FuelReceive(['site_id' => $siteId, 'receive_at' => now(), 'fuel_type' => 'diesel', 'status' => 'draft']);
        return view('admin.fuel.receives.create', compact('receive', 'sites', 'tanks', 'siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'required|uuid|exists:sites,id',
            'tank_id' => 'nullable|uuid|exists:fuel_tanks,id',
            'supplier' => 'nullable|string|max:200',
            'po_number' => 'nullable|string|max:100',
            'invoice_number' => 'nullable|string|max:100',
            'receive_at' => 'required|date',
            'volume' => 'required|numeric|min:0.01',
            'fuel_type' => 'required|string|max:50',
            'price_per_unit' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'vehicle_number' => 'nullable|string|max:50',
            'driver_name' => 'nullable|string|max:100',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'status' => 'required|string|max:20',
        ]);

        $data['created_by'] = $request->user()->id;
        $receive = FuelReceive::create($data);

        return redirect()->route('fuel.receives.index', ['site' => $receive->site_id])
            ->with('success', 'Fuel Receive created.');
    }

    public function edit(FuelReceive $receive)
    {
        $siteId = $receive->site_id;
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $tanks = FuelTank::when($siteId, fn($q) => $q->where('site_id', $siteId))->orderBy('code')->get(['id', 'code', 'name']);
        return view('admin.fuel.receives.edit', compact('receive', 'sites', 'tanks', 'siteId'));
    }

    public function update(Request $request, FuelReceive $receive)
    {
        $data = $request->validate([
            'site_id' => 'required|uuid|exists:sites,id',
            'tank_id' => 'nullable|uuid|exists:fuel_tanks,id',
            'supplier' => 'nullable|string|max:200',
            'po_number' => 'nullable|string|max:100',
            'invoice_number' => 'nullable|string|max:100',
            'receive_at' => 'required|date',
            'volume' => 'required|numeric|min:0.01',
            'fuel_type' => 'required|string|max:50',
            'price_per_unit' => 'nullable|numeric|min:0',
            'total_amount' => 'nullable|numeric|min:0',
            'vehicle_number' => 'nullable|string|max:50',
            'driver_name' => 'nullable|string|max:100',
            'reference_no' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
            'status' => 'required|string|max:20',
        ]);

        $receive->update($data);
        return redirect()->route('fuel.receives.index', ['site' => $receive->site_id])
            ->with('success', 'Fuel Receive updated.');
    }

    public function destroy(FuelReceive $receive)
    {
        $siteId = $receive->site_id;
        $receive->delete();
        return redirect()->route('fuel.receives.index', ['site' => $siteId])
            ->with('success', 'Fuel Receive deleted.');
    }
}
