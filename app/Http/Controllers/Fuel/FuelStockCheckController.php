<?php

namespace App\Http\Controllers\Fuel;

use App\Http\Controllers\Controller;
use App\Models\Fuel\FuelStockCheck;
use App\Models\Fuel\FuelTank;
use App\Models\Site;
use Illuminate\Http\Request;

class FuelStockCheckController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(FuelStockCheck::class, 'stockCheck');
    }

    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $items = FuelStockCheck::with(['site', 'tank', 'checker'])
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->when($request->filled('from'), fn($q) => $q->where('check_at', '>=', $request->from))
            ->when($request->filled('to'), fn($q) => $q->where('check_at', '<=', $request->to))
            ->orderByDesc('check_at')
            ->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $tanks = FuelTank::when($siteId, fn($q) => $q->where('site_id', $siteId))->orderBy('code')->get(['id', 'code', 'name']);
        return view('admin.fuel.stock-checks.index', compact('items', 'sites', 'tanks', 'siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $tanks = FuelTank::when($siteId, fn($q) => $q->where('site_id', $siteId))->orderBy('code')->get(['id', 'code', 'name']);
        $check = new FuelStockCheck(['site_id' => $siteId, 'check_at' => now(), 'uom' => 'liter']);
        return view('admin.fuel.stock-checks.create', compact('check', 'sites', 'tanks', 'siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'required|uuid|exists:sites,id',
            'tank_id' => 'required|uuid|exists:fuel_tanks,id',
            'check_at' => 'required|date',
            'book_volume' => 'required|numeric|min:0',
            'actual_volume' => 'required|numeric|min:0',
            'difference' => 'required|numeric',
            'uom' => 'required|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $data['checked_by'] = $request->user()->id;
        FuelStockCheck::create($data);

        return redirect()->route('fuel.stock-checks.index', ['site' => $data['site_id']])
            ->with('success', 'Stock Check created.');
    }

    public function edit(FuelStockCheck $stockCheck)
    {
        $siteId = $stockCheck->site_id;
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $tanks = FuelTank::when($siteId, fn($q) => $q->where('site_id', $siteId))->orderBy('code')->get(['id', 'code', 'name']);
        return view('admin.fuel.stock-checks.edit', compact('stockCheck', 'sites', 'tanks', 'siteId'));
    }

    public function update(Request $request, FuelStockCheck $stockCheck)
    {
        $data = $request->validate([
            'site_id' => 'required|uuid|exists:sites,id',
            'tank_id' => 'required|uuid|exists:fuel_tanks,id',
            'check_at' => 'required|date',
            'book_volume' => 'required|numeric|min:0',
            'actual_volume' => 'required|numeric|min:0',
            'difference' => 'required|numeric',
            'uom' => 'required|string|max:20',
            'notes' => 'nullable|string',
        ]);

        $stockCheck->update($data);
        return redirect()->route('fuel.stock-checks.index', ['site' => $stockCheck->site_id])
            ->with('success', 'Stock Check updated.');
    }

    public function destroy(FuelStockCheck $stockCheck)
    {
        $siteId = $stockCheck->site_id;
        $stockCheck->delete();
        return redirect()->route('fuel.stock-checks.index', ['site' => $siteId])
            ->with('success', 'Stock Check deleted.');
    }
}
