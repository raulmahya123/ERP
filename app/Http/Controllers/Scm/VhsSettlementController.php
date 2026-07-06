<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Models\Scm\VhsSettlement;
use App\Models\Scm\PurchaseOrder;
use App\Models\Site;
use Illuminate\Http\Request;

class VhsSettlementController extends Controller
{
    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $q = VhsSettlement::with(['site'])
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($request->filled('settlement_number'), fn($qq) => $qq->where('settlement_number', 'like', '%' . $request->settlement_number . '%'))
            ->when($request->filled('status'), fn($qq) => $qq->where('status', $request->status))
            ->when($request->filled('from'), fn($qq) => $qq->where('settlement_date', '>=', $request->from))
            ->when($request->filled('to'), fn($qq) => $qq->where('settlement_date', '<=', $request->to))
            ->orderByDesc('created_at');

        $items = $q->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $purchaseOrders = PurchaseOrder::where('status', 'approved')->orderBy('po_number')->get(['id', 'po_number']);

        return view('admin.scm.vhs-settlements.index', compact('items', 'sites', 'purchaseOrders', 'siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $purchaseOrders = PurchaseOrder::where('status', 'approved')->orderBy('po_number')->get(['id', 'po_number']);

        $vhsSettlement = new VhsSettlement([
            'site_id' => $siteId,
            'settlement_date' => now(),
            'status' => 'draft',
        ]);

        return view('admin.scm.vhs-settlements.create', compact(
            'vhsSettlement', 'sites', 'purchaseOrders', 'siteId'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'required|string',
            'settlement_number' => 'required|string|max:50|unique:scm_vhs_settlements,settlement_number',
            'purchase_order_id' => 'required|string',
            'total_amount' => 'nullable|numeric',
            'paid_amount' => 'nullable|numeric',
            'balance' => 'nullable|numeric',
            'settlement_date' => 'required|date',
            'status' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        VhsSettlement::create($data);

        return redirect()
            ->route('scm.vhs_settlements.index', ['site' => $data['site_id']])
            ->with('success', 'VHS Settlement tersimpan.');
    }

    public function edit(Request $request, VhsSettlement $vhs_settlement)
    {
        $siteId = $vhs_settlement->site_id ?: ($request->query('site') ?: session('site_id'));
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $purchaseOrders = PurchaseOrder::where('status', 'approved')->orderBy('po_number')->get(['id', 'po_number']);

        return view('admin.scm.vhs-settlements.edit', compact(
            'vhs_settlement', 'sites', 'purchaseOrders', 'siteId'
        ));
    }

    public function update(Request $request, VhsSettlement $vhs_settlement)
    {
        $data = $request->validate([
            'site_id' => 'required|string',
            'settlement_number' => 'required|string|max:50|unique:scm_vhs_settlements,settlement_number,' . $vhs_settlement->id,
            'purchase_order_id' => 'required|string',
            'total_amount' => 'nullable|numeric',
            'paid_amount' => 'nullable|numeric',
            'balance' => 'nullable|numeric',
            'settlement_date' => 'required|date',
            'status' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $vhs_settlement->update($data);

        return redirect()
            ->route('scm.vhs_settlements.index', ['site' => $data['site_id']])
            ->with('success', 'VHS Settlement diperbarui.');
    }

    public function destroy(Request $request, VhsSettlement $vhs_settlement)
    {
        $siteId = $vhs_settlement->site_id;
        $vhs_settlement->delete();

        return redirect()
            ->route('scm.vhs_settlements.index', ['site' => $siteId])
            ->with('success', 'VHS Settlement dihapus.');
    }

    public function post(Request $request, VhsSettlement $vhs_settlement)
    {
        $vhs_settlement->update([
            'status' => 'posted',
        ]);

        return redirect()
            ->route('scm.vhs_settlements.index', ['site' => $vhs_settlement->site_id])
            ->with('success', 'VHS Settlement diposting.');
    }
}
