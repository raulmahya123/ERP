<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Models\Scm\PurchaseRequest;
use App\Models\Scm\PurchaseRequestItem;
use App\Models\Scm\MaterialMaster;
use App\Models\Site;
use Illuminate\Http\Request;

class PurchaseRequestController extends Controller
{
    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $q = PurchaseRequest::with(['site'])
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($request->filled('pr_number'), fn($qq) => $qq->where('pr_number', 'like', '%' . $request->pr_number . '%'))
            ->when($request->filled('status'), fn($qq) => $qq->where('status', $request->status))
            ->when($request->filled('from'), fn($qq) => $qq->where('request_date', '>=', $request->from))
            ->when($request->filled('to'), fn($qq) => $qq->where('request_date', '<=', $request->to))
            ->orderByDesc('created_at');

        $items = $q->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        return view('admin.scm.purchase-requests.index', compact('items', 'sites', 'siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $materials = MaterialMaster::where('is_active', true)->orderBy('material_name')->get(['id', 'material_code', 'material_name', 'base_uom']);

        $purchaseRequest = new PurchaseRequest([
            'site_id' => $siteId,
            'request_date' => now(),
            'status' => 'draft',
        ]);

        return view('admin.scm.purchase-requests.create', compact(
            'purchaseRequest', 'sites', 'materials', 'siteId'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'required|string',
            'pr_number' => 'required|string|max:50|unique:scm_purchase_requests,pr_number',
            'request_date' => 'required|date',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'requested_by' => 'nullable|string',
        ]);

        PurchaseRequest::create($data);

        return redirect()
            ->route('scm.purchase_requests.index', ['site' => $data['site_id']])
            ->with('success', 'Purchase Request tersimpan.');
    }

    public function edit(Request $request, PurchaseRequest $purchase_request)
    {
        $siteId = $purchase_request->site_id ?: ($request->query('site') ?: session('site_id'));
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $materials = MaterialMaster::where('is_active', true)->orderBy('material_name')->get(['id', 'material_code', 'material_name', 'base_uom']);

        return view('admin.scm.purchase-requests.edit', compact(
            'purchase_request', 'sites', 'materials', 'siteId'
        ));
    }

    public function update(Request $request, PurchaseRequest $purchase_request)
    {
        $data = $request->validate([
            'site_id' => 'required|string',
            'pr_number' => 'required|string|max:50|unique:scm_purchase_requests,pr_number,' . $purchase_request->id,
            'request_date' => 'required|date',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'requested_by' => 'nullable|string',
        ]);

        $purchase_request->update($data);

        return redirect()
            ->route('scm.purchase_requests.index', ['site' => $data['site_id']])
            ->with('success', 'Purchase Request diperbarui.');
    }

    public function destroy(Request $request, PurchaseRequest $purchase_request)
    {
        $siteId = $purchase_request->site_id;
        $purchase_request->delete();

        return redirect()
            ->route('scm.purchase_requests.index', ['site' => $siteId])
            ->with('success', 'Purchase Request dihapus.');
    }
}
