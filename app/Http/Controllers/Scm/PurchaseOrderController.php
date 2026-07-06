<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Models\Scm\PurchaseOrder;
use App\Models\Scm\PurchaseOrderItem;
use App\Models\Scm\MaterialMaster;
use App\Models\Site;
use App\Models\MasterRecord;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $q = PurchaseOrder::with(['site', 'vendor'])
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($request->filled('po_number'), fn($qq) => $qq->where('po_number', 'like', '%' . $request->po_number . '%'))
            ->when($request->filled('status'), fn($qq) => $qq->where('status', $request->status))
            ->when($request->filled('from'), fn($qq) => $qq->where('order_date', '>=', $request->from))
            ->when($request->filled('to'), fn($qq) => $qq->where('order_date', '<=', $request->to))
            ->orderByDesc('created_at');

        $items = $q->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        return view('admin.scm.purchase-orders.index', compact('items', 'sites', 'siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $materials = MaterialMaster::where('is_active', true)->orderBy('material_name')->get(['id', 'material_code', 'material_name', 'base_uom']);
        $vendors = MasterRecord::orderBy('name')->get(['id', 'code', 'name']);

        $purchaseOrder = new PurchaseOrder([
            'site_id' => $siteId,
            'order_date' => now(),
            'status' => 'draft',
        ]);

        return view('admin.scm.purchase-orders.create', compact(
            'purchaseOrder', 'sites', 'materials', 'vendors', 'siteId'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'required|string',
            'po_number' => 'required|string|max:50|unique:scm_purchase_orders,po_number',
            'vendor_id' => 'required|string',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'payment_terms' => 'nullable|string',
            'shipping_method' => 'nullable|string',
            'total_amount' => 'nullable|numeric',
            'currency' => 'nullable|string',
            'status' => 'nullable|string',
            'notes' => 'nullable|string',
            'created_by' => 'nullable|string',
        ]);

        $data['created_by'] ??= $request->user()?->id;

        PurchaseOrder::create($data);

        return redirect()
            ->route('scm.purchase_orders.index', ['site' => $data['site_id']])
            ->with('success', 'Purchase Order tersimpan.');
    }

    public function edit(Request $request, PurchaseOrder $purchase_order)
    {
        $siteId = $purchase_order->site_id ?: ($request->query('site') ?: session('site_id'));
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $materials = MaterialMaster::where('is_active', true)->orderBy('material_name')->get(['id', 'material_code', 'material_name', 'base_uom']);
        $vendors = MasterRecord::orderBy('name')->get(['id', 'code', 'name']);

        return view('admin.scm.purchase-orders.edit', compact(
            'purchase_order', 'sites', 'materials', 'vendors', 'siteId'
        ));
    }

    public function update(Request $request, PurchaseOrder $purchase_order)
    {
        $data = $request->validate([
            'site_id' => 'required|string',
            'po_number' => 'required|string|max:50|unique:scm_purchase_orders,po_number,' . $purchase_order->id,
            'vendor_id' => 'required|string',
            'order_date' => 'required|date',
            'delivery_date' => 'nullable|date',
            'payment_terms' => 'nullable|string',
            'shipping_method' => 'nullable|string',
            'total_amount' => 'nullable|numeric',
            'currency' => 'nullable|string',
            'status' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $purchase_order->update($data);

        return redirect()
            ->route('scm.purchase_orders.index', ['site' => $data['site_id']])
            ->with('success', 'Purchase Order diperbarui.');
    }

    public function destroy(Request $request, PurchaseOrder $purchase_order)
    {
        $siteId = $purchase_order->site_id;
        $purchase_order->delete();

        return redirect()
            ->route('scm.purchase_orders.index', ['site' => $siteId])
            ->with('success', 'Purchase Order dihapus.');
    }

    public function approve(Request $request, PurchaseOrder $purchase_order)
    {
        $purchase_order->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('scm.purchase_orders.index', ['site' => $purchase_order->site_id])
            ->with('success', 'Purchase Order disetujui.');
    }
}
