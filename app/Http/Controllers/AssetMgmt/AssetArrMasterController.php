<?php

namespace App\Http\Controllers\AssetMgmt;

use App\Http\Controllers\Controller;
use App\Models\AssetMgmt\AssetArrMaster;
use App\Models\AssetMgmt\AssetArrApproval;
use App\Models\Asset;
use App\Models\Site;
use Illuminate\Http\Request;

class AssetArrMasterController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(AssetArrMaster::class, 'arr');
    }

    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $q = AssetArrMaster::query()
            ->with(['site', 'asset', 'requestedBy'])
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($request->filled('status'), fn($qq) => $qq->where('status', $request->status))
            ->when($request->filled('arr_type'), fn($qq) => $qq->where('arr_type', $request->arr_type))
            ->when($request->filled('asset_id'), fn($qq) => $qq->where('asset_id', $request->asset_id))
            ->orderByDesc('created_at');

        $items = $q->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $assets = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))->orderBy('code')->get(['id','code','name']);
        $arrTypes = ['repair' => 'Repair', 'replacement' => 'Replacement', 'modification' => 'Modification', 'overhaul' => 'Overhaul', 'other' => 'Other'];
        $statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];

        return view('admin.asset-mgmt.asset-arr-masters.index', compact('items','sites','assets','arrTypes','statuses','siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $assets = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))->orderBy('code')->get(['id','code','name']);
        $arrTypes = ['repair' => 'Repair', 'replacement' => 'Replacement', 'modification' => 'Modification', 'overhaul' => 'Overhaul', 'other' => 'Other'];
        $statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];

        $assetArrMaster = new AssetArrMaster([
            'site_id'      => $siteId,
            'request_date' => now(),
            'status'       => 'draft',
        ]);

        return view('admin.asset-mgmt.asset-arr-masters.create', compact('assetArrMaster','sites','assets','arrTypes','statuses','siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id'      => 'required|uuid|exists:sites,id',
            'arr_number'   => 'required|string|max:50|unique:asset_arr_masters,arr_number',
            'asset_id'     => 'required|uuid|exists:assets,id',
            'request_date' => 'required|date',
            'arr_type'     => 'required|string|max:50',
            'reason'       => 'nullable|string',
            'status'       => 'required|string|max:50',
            'notes'        => 'nullable|string',
        ]);

        $data['requested_by'] = $request->user()->id;

        AssetArrMaster::create($data);

        return redirect()
            ->route('asset_mgmt.asset_arr_masters.index', ['site' => $data['site_id']])
            ->with('success', 'ARR tersimpan.');
    }

    public function edit(Request $request, AssetArrMaster $assetArrMaster)
    {
        $siteId = $assetArrMaster->site_id ?: ($request->query('site') ?: session('site_id'));

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $assets = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))->orderBy('code')->get(['id','code','name']);
        $arrTypes = ['repair' => 'Repair', 'replacement' => 'Replacement', 'modification' => 'Modification', 'overhaul' => 'Overhaul', 'other' => 'Other'];
        $statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];

        return view('admin.asset-mgmt.asset-arr-masters.edit', compact('assetArrMaster','sites','assets','arrTypes','statuses','siteId'));
    }

    public function update(Request $request, AssetArrMaster $assetArrMaster)
    {
        $data = $request->validate([
            'site_id'      => 'required|uuid|exists:sites,id',
            'arr_number'   => 'required|string|max:50|unique:asset_arr_masters,arr_number,'.$assetArrMaster->id,
            'asset_id'     => 'required|uuid|exists:assets,id',
            'request_date' => 'required|date',
            'arr_type'     => 'required|string|max:50',
            'reason'       => 'nullable|string',
            'status'       => 'required|string|max:50',
            'notes'        => 'nullable|string',
        ]);

        $assetArrMaster->update($data);

        return redirect()
            ->route('asset_mgmt.asset_arr_masters.index', ['site' => $data['site_id']])
            ->with('success', 'ARR diperbarui.');
    }

    public function destroy(Request $request, AssetArrMaster $assetArrMaster)
    {
        $siteId = $assetArrMaster->site_id;
        $assetArrMaster->delete();

        return redirect()
            ->route('asset_mgmt.asset_arr_masters.index', ['site' => $siteId])
            ->with('success', 'ARR dihapus.');
    }

    public function approve(Request $request, AssetArrMaster $assetArrMaster)
    {
        $data = $request->validate([
            'notes' => 'nullable|string',
        ]);

        AssetArrApproval::create([
            'arr_id'      => $assetArrMaster->id,
            'approver_id' => $request->user()->id,
            'status'      => 'approved',
            'notes'       => $data['notes'] ?? null,
            'action_at'   => now(),
        ]);

        $assetArrMaster->update(['status' => 'approved']);

        return redirect()
            ->route('asset_mgmt.asset_arr_masters.index')
            ->with('success', 'ARR disetujui.');
    }
}
