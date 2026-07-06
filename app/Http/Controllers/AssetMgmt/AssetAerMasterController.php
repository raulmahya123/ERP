<?php

namespace App\Http\Controllers\AssetMgmt;

use App\Http\Controllers\Controller;
use App\Models\AssetMgmt\AssetAerMaster;
use App\Models\AssetMgmt\AssetAerApproval;
use App\Models\Asset;
use App\Models\Site;
use Illuminate\Http\Request;

class AssetAerMasterController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(AssetAerMaster::class, 'aer');
    }

    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $q = AssetAerMaster::query()
            ->with(['site', 'asset', 'requestedBy'])
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($request->filled('status'), fn($qq) => $qq->where('status', $request->status))
            ->when($request->filled('asset_id'), fn($qq) => $qq->where('asset_id', $request->asset_id))
            ->orderByDesc('created_at');

        $items = $q->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $assets = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))->orderBy('code')->get(['id','code','name']);
        $statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'executed' => 'Executed', 'cancelled' => 'Cancelled'];

        return view('admin.asset-mgmt.asset-aer-masters.index', compact('items','sites','assets','statuses','siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $assets = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))->orderBy('code')->get(['id','code','name']);
        $statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'executed' => 'Executed', 'cancelled' => 'Cancelled'];

        $assetAerMaster = new AssetAerMaster([
            'site_id'      => $siteId,
            'request_date' => now(),
            'status'       => 'draft',
        ]);

        return view('admin.asset-mgmt.asset-aer-masters.create', compact('assetAerMaster','sites','assets','statuses','siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id'              => 'required|uuid|exists:sites,id',
            'aer_number'           => 'required|string|max:50|unique:asset_aer_masters,aer_number',
            'asset_id'             => 'required|uuid|exists:assets,id',
            'request_date'         => 'required|date',
            'estimated_return_date' => 'nullable|date|after_or_equal:request_date',
            'reason'               => 'nullable|string',
            'status'               => 'required|string|max:50',
        ]);

        $data['requested_by'] = $request->user()->id;

        AssetAerMaster::create($data);

        return redirect()
            ->route('asset_mgmt.asset_aer_masters.index', ['site' => $data['site_id']])
            ->with('success', 'AER tersimpan.');
    }

    public function edit(Request $request, AssetAerMaster $assetAerMaster)
    {
        $siteId = $assetAerMaster->site_id ?: ($request->query('site') ?: session('site_id'));

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $assets = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))->orderBy('code')->get(['id','code','name']);
        $statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'executed' => 'Executed', 'cancelled' => 'Cancelled'];

        return view('admin.asset-mgmt.asset-aer-masters.edit', compact('assetAerMaster','sites','assets','statuses','siteId'));
    }

    public function update(Request $request, AssetAerMaster $assetAerMaster)
    {
        $data = $request->validate([
            'site_id'              => 'required|uuid|exists:sites,id',
            'aer_number'           => 'required|string|max:50|unique:asset_aer_masters,aer_number,'.$assetAerMaster->id,
            'asset_id'             => 'required|uuid|exists:assets,id',
            'request_date'         => 'required|date',
            'estimated_return_date' => 'nullable|date|after_or_equal:request_date',
            'reason'               => 'nullable|string',
            'status'               => 'required|string|max:50',
        ]);

        $assetAerMaster->update($data);

        return redirect()
            ->route('asset_mgmt.asset_aer_masters.index', ['site' => $data['site_id']])
            ->with('success', 'AER diperbarui.');
    }

    public function destroy(Request $request, AssetAerMaster $assetAerMaster)
    {
        $siteId = $assetAerMaster->site_id;
        $assetAerMaster->delete();

        return redirect()
            ->route('asset_mgmt.asset_aer_masters.index', ['site' => $siteId])
            ->with('success', 'AER dihapus.');
    }

    public function approve(Request $request, AssetAerMaster $assetAerMaster)
    {
        $data = $request->validate([
            'notes' => 'nullable|string',
        ]);

        AssetAerApproval::create([
            'aer_id'      => $assetAerMaster->id,
            'approver_id' => $request->user()->id,
            'status'      => 'approved',
            'notes'       => $data['notes'] ?? null,
            'action_at'   => now(),
        ]);

        $assetAerMaster->update(['status' => 'approved']);

        return redirect()
            ->route('asset_mgmt.asset_aer_masters.index')
            ->with('success', 'AER disetujui.');
    }
}
