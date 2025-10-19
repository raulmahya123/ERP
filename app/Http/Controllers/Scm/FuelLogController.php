<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFuelLogRequest;
use App\Http\Requests\UpdateFuelLogRequest;
use App\Models\Scm\FuelLog;
use App\Models\{Site, Asset, User};
use Illuminate\Http\Request;

class FuelLogController extends Controller
{
    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $q = FuelLog::query()
            ->with(['site','unit','operator'])
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($request->filled('from'), fn($qq) => $qq->where('dispensed_at','>=',$request->from))
            ->when($request->filled('to'),   fn($qq) => $qq->where('dispensed_at','<=',$request->to))
            ->when($request->filled('unit_id'), fn($qq) => $qq->where('unit_id',$request->unit_id))
            ->when($request->filled('fuel_type'), fn($qq) => $qq->where('fuel_type',$request->fuel_type))
            ->when($request->filled('operator_id'), fn($qq) => $qq->where('operator_id',$request->operator_id))
            ->orderByDesc('dispensed_at')
            ->orderBy('unit_id');

        $items = $q->paginate(15)->withQueryString();

        // dropdowns
        $sites = Site::orderBy('code')->get(['id','code','name']);
        $units = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))
                      ->orderBy('code')->get(['id','code','name']);
        $operators = User::orderBy('name')->get(['id','name']);

        $fuelTypes = ['diesel' => 'Diesel', 'gasoline' => 'Gasoline', 'other' => 'Other'];

        return view('admin.scm.fuel-logs.index', compact(
            'items','sites','units','operators','fuelTypes','siteId'
        ));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $units = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))
                      ->orderBy('code')->get(['id','code','name']);
        $operators = User::orderBy('name')->get(['id','name']);
        $fuelTypes = ['diesel' => 'Diesel', 'gasoline' => 'Gasoline', 'other' => 'Other'];

        $fuelLog = new FuelLog([
            'site_id'      => $siteId,
            'dispensed_at' => now(),
            'fuel_type'    => 'diesel',
        ]);

        return view('admin.scm.fuel-logs.create', compact(
            'fuelLog','sites','units','operators','fuelTypes','siteId'
        ));
    }

    public function store(StoreFuelLogRequest $request)
    {
        $data = $request->validated();

        FuelLog::create([
            'site_id'      => $data['site_id'],
            'unit_id'      => $data['unit_id'],
            'operator_id'  => $data['operator_id'] ?? null,
            'dispensed_at' => $data['dispensed_at'],
            'fuel_type'    => $data['fuel_type'],
            'liter'        => $data['liter'],
            'dispenser_id' => $data['dispenser_id'] ?? null,
            'receipt_no'   => $data['receipt_no'] ?? null,
            'client_uid'   => $data['client_uid'] ?? null,
            'created_by'   => $request->user()->id,
        ]);

        return redirect()
            ->route('scm.fuel_logs.index', ['site' => $data['site_id']])
            ->with('success', 'Fuel Log tersimpan.');
    }

    public function edit(Request $request, FuelLog $fuel_log)
    {
        $siteId = $fuel_log->site_id ?: ($request->query('site') ?: session('site_id'));

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $units = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))
                      ->orderBy('code')->get(['id','code','name']);
        $operators = User::orderBy('name')->get(['id','name']);
        $fuelTypes = ['diesel' => 'Diesel', 'gasoline' => 'Gasoline', 'other' => 'Other'];

        return view('admin.scm.fuel-logs.edit', compact(
            'fuel_log','sites','units','operators','fuelTypes','siteId'
        ));
    }

    public function update(UpdateFuelLogRequest $request, FuelLog $fuel_log)
    {
        $data = $request->validated();

        $fuel_log->update([
            'site_id'      => $data['site_id'],
            'unit_id'      => $data['unit_id'],
            'operator_id'  => $data['operator_id'] ?? null,
            'dispensed_at' => $data['dispensed_at'],
            'fuel_type'    => $data['fuel_type'],
            'liter'        => $data['liter'],
            'dispenser_id' => $data['dispenser_id'] ?? null,
            'receipt_no'   => $data['receipt_no'] ?? null,
            'client_uid'   => $data['client_uid'] ?? null,
        ]);

        return redirect()
            ->route('scm.fuel_logs.index', ['site' => $data['site_id']])
            ->with('success', 'Fuel Log diperbarui.');
    }

    public function destroy(Request $request, FuelLog $fuel_log)
    {
        $siteId = $fuel_log->site_id;
        $fuel_log->delete();

        return redirect()
            ->route('scm.fuel_logs.index', ['site' => $siteId])
            ->with('success', 'Fuel Log dihapus.');
    }
}
