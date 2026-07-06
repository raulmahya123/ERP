<?php

namespace App\Http\Controllers\Fuel;

use App\Http\Controllers\Controller;
use App\Models\Fuel\FuelInventoryBalance;
use App\Models\Fuel\FuelTank;
use App\Models\Site;
use Illuminate\Http\Request;

class FuelInventoryBalanceController extends Controller
{
    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $items = FuelInventoryBalance::with(['site', 'tank'])
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->when($request->filled('from'), fn($q) => $q->where('balance_date', '>=', $request->from))
            ->when($request->filled('to'), fn($q) => $q->where('balance_date', '<=', $request->to))
            ->when($request->filled('tank_id'), fn($q) => $q->where('tank_id', $request->tank_id))
            ->orderByDesc('balance_date')
            ->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $tanks = FuelTank::when($siteId, fn($q) => $q->where('site_id', $siteId))->orderBy('code')->get(['id', 'code', 'name']);
        return view('admin.fuel.inventory-balances.index', compact('items', 'sites', 'tanks', 'siteId'));
    }
}
