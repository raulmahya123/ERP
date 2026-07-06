<?php

namespace App\Http\Controllers\Fuel;

use App\Http\Controllers\Controller;
use App\Models\Fuel\FuelTankHistory;
use App\Models\Fuel\FuelTank;
use App\Models\Site;
use Illuminate\Http\Request;

class FuelTankHistoryController extends Controller
{
    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $items = FuelTankHistory::with(['site', 'tank'])
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->when($request->filled('tank_id'), fn($q) => $q->where('tank_id', $request->tank_id))
            ->when($request->filled('transaction_type'), fn($q) => $q->where('transaction_type', $request->transaction_type))
            ->when($request->filled('from'), fn($q) => $q->where('transaction_at', '>=', $request->from))
            ->when($request->filled('to'), fn($q) => $q->where('transaction_at', '<=', $request->to))
            ->orderByDesc('transaction_at')
            ->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $tanks = FuelTank::when($siteId, fn($q) => $q->where('site_id', $siteId))->orderBy('code')->get(['id', 'code', 'name']);
        return view('admin.fuel.tank-histories.index', compact('items', 'sites', 'tanks', 'siteId'));
    }
}
