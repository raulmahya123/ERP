<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Models\Scm\PurchaseInfoRecord;
use App\Models\Scm\MaterialMaster;
use App\Models\Site;
use App\Models\MasterRecord;
use Illuminate\Http\Request;

class PurchaseInfoRecordController extends Controller
{
    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $q = PurchaseInfoRecord::with(['site', 'material', 'vendor'])
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($request->filled('material_id'), fn($qq) => $qq->where('material_id', $request->material_id))
            ->when($request->filled('info_category'), fn($qq) => $qq->where('info_category', $request->info_category))
            ->when($request->filled('status'), fn($qq) => $qq->where('status', $request->status))
            ->orderByDesc('created_at');

        $items = $q->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $materials = MaterialMaster::orderBy('material_name')->get(['id', 'material_code', 'material_name']);
        $vendors = MasterRecord::orderBy('name')->get(['id', 'code', 'name']);
        $categories = ['price_quote' => 'Price Quote', 'catalog' => 'Catalog', 'contract' => 'Contract', 'other' => 'Other'];

        return view('admin.scm.purchase-info-records.index', compact(
            'items', 'sites', 'materials', 'vendors', 'categories', 'siteId'
        ));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $materials = MaterialMaster::orderBy('material_name')->get(['id', 'material_code', 'material_name']);
        $vendors = MasterRecord::orderBy('name')->get(['id', 'code', 'name']);
        $categories = ['price_quote' => 'Price Quote', 'catalog' => 'Catalog', 'contract' => 'Contract', 'other' => 'Other'];

        $purchaseInfoRecord = new PurchaseInfoRecord(['site_id' => $siteId, 'status' => 'active']);

        return view('admin.scm.purchase-info-records.create', compact(
            'purchaseInfoRecord', 'sites', 'materials', 'vendors', 'categories', 'siteId'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'required|string',
            'material_id' => 'required|string',
            'vendor_id' => 'required|string',
            'info_category' => 'required|string',
            'price' => 'nullable|numeric',
            'currency' => 'nullable|string',
            'uom' => 'nullable|string',
            'min_order_qty' => 'nullable|numeric',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date',
            'status' => 'nullable|string',
        ]);

        PurchaseInfoRecord::create($data);

        return redirect()
            ->route('scm.purchase_info_records.index', ['site' => $data['site_id']])
            ->with('success', 'Purchase Info Record tersimpan.');
    }

    public function edit(Request $request, PurchaseInfoRecord $purchase_info_record)
    {
        $siteId = $purchase_info_record->site_id ?: ($request->query('site') ?: session('site_id'));
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $materials = MaterialMaster::orderBy('material_name')->get(['id', 'material_code', 'material_name']);
        $vendors = MasterRecord::orderBy('name')->get(['id', 'code', 'name']);
        $categories = ['price_quote' => 'Price Quote', 'catalog' => 'Catalog', 'contract' => 'Contract', 'other' => 'Other'];

        return view('admin.scm.purchase-info-records.edit', compact(
            'purchase_info_record', 'sites', 'materials', 'vendors', 'categories', 'siteId'
        ));
    }

    public function update(Request $request, PurchaseInfoRecord $purchase_info_record)
    {
        $data = $request->validate([
            'site_id' => 'required|string',
            'material_id' => 'required|string',
            'vendor_id' => 'required|string',
            'info_category' => 'required|string',
            'price' => 'nullable|numeric',
            'currency' => 'nullable|string',
            'uom' => 'nullable|string',
            'min_order_qty' => 'nullable|numeric',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date',
            'status' => 'nullable|string',
        ]);

        $purchase_info_record->update($data);

        return redirect()
            ->route('scm.purchase_info_records.index', ['site' => $data['site_id']])
            ->with('success', 'Purchase Info Record diperbarui.');
    }

    public function destroy(Request $request, PurchaseInfoRecord $purchase_info_record)
    {
        $siteId = $purchase_info_record->site_id;
        $purchase_info_record->delete();

        return redirect()
            ->route('scm.purchase_info_records.index', ['site' => $siteId])
            ->with('success', 'Purchase Info Record dihapus.');
    }
}
