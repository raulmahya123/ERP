<?php

namespace App\Http\Controllers\Fuel;

use App\Http\Controllers\Controller;
use App\Models\Fuel\FuelAdjustment;
use App\Models\Fuel\FuelAdjustmentApproval;
use App\Models\Fuel\FuelTank;
use App\Models\Site;
use Illuminate\Http\Request;

class FuelAdjustmentController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(FuelAdjustment::class, 'adjustment');
    }

    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $items = FuelAdjustment::with(['site', 'tank', 'requester', 'approver'])
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->when($request->filled('status'), fn($q) => $q->where('status', $request->status))
            ->orderByDesc('adjustment_at')
            ->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        return view('admin.fuel.adjustments.index', compact('items', 'sites', 'siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $tanks = FuelTank::when($siteId, fn($q) => $q->where('site_id', $siteId))->orderBy('code')->get(['id', 'code', 'name']);
        $adjustment = new FuelAdjustment(['site_id' => $siteId, 'adjustment_at' => now(), 'status' => 'pending']);
        return view('admin.fuel.adjustments.create', compact('adjustment', 'sites', 'tanks', 'siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'required|uuid|exists:sites,id',
            'tank_id' => 'required|uuid|exists:fuel_tanks,id',
            'adjustment_at' => 'required|date',
            'volume' => 'required|numeric',
            'adjustment_type' => 'required|string|max:20',
            'reason' => 'nullable|string|max:500',
            'status' => 'required|string|max:20',
        ]);

        $data['requested_by'] = $request->user()->id;
        FuelAdjustment::create($data);

        return redirect()->route('fuel.adjustments.index', ['site' => $data['site_id']])
            ->with('success', 'Fuel Adjustment created.');
    }

    public function edit(FuelAdjustment $adjustment)
    {
        $siteId = $adjustment->site_id;
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $tanks = FuelTank::when($siteId, fn($q) => $q->where('site_id', $siteId))->orderBy('code')->get(['id', 'code', 'name']);
        return view('admin.fuel.adjustments.edit', compact('adjustment', 'sites', 'tanks', 'siteId'));
    }

    public function update(Request $request, FuelAdjustment $adjustment)
    {
        $data = $request->validate([
            'site_id' => 'required|uuid|exists:sites,id',
            'tank_id' => 'required|uuid|exists:fuel_tanks,id',
            'adjustment_at' => 'required|date',
            'volume' => 'required|numeric',
            'adjustment_type' => 'required|string|max:20',
            'reason' => 'nullable|string|max:500',
            'status' => 'required|string|max:20',
        ]);

        $adjustment->update($data);
        return redirect()->route('fuel.adjustments.index', ['site' => $adjustment->site_id])
            ->with('success', 'Fuel Adjustment updated.');
    }

    public function destroy(FuelAdjustment $adjustment)
    {
        $siteId = $adjustment->site_id;
        $adjustment->delete();
        return redirect()->route('fuel.adjustments.index', ['site' => $siteId])
            ->with('success', 'Fuel Adjustment deleted.');
    }

    public function approve(Request $request, FuelAdjustment $adjustment)
    {
        $data = $request->validate(['approval_notes' => 'nullable|string|max:500']);

        $adjustment->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
            'approval_notes' => $data['approval_notes'] ?? null,
        ]);

        FuelAdjustmentApproval::create([
            'adjustment_id' => $adjustment->id,
            'approver_id' => $request->user()->id,
            'status' => 'approved',
            'notes' => $data['approval_notes'] ?? null,
            'action_at' => now(),
        ]);

        return redirect()->route('fuel.adjustments.index', ['site' => $adjustment->site_id])
            ->with('success', 'Fuel Adjustment approved.');
    }

    public function reject(Request $request, FuelAdjustment $adjustment)
    {
        $data = $request->validate(['approval_notes' => 'nullable|string|max:500']);

        $adjustment->update([
            'status' => 'rejected',
            'approval_notes' => $data['approval_notes'] ?? null,
        ]);

        FuelAdjustmentApproval::create([
            'adjustment_id' => $adjustment->id,
            'approver_id' => $request->user()->id,
            'status' => 'rejected',
            'notes' => $data['approval_notes'] ?? null,
            'action_at' => now(),
        ]);

        return redirect()->route('fuel.adjustments.index', ['site' => $adjustment->site_id])
            ->with('success', 'Fuel Adjustment rejected.');
    }
}
