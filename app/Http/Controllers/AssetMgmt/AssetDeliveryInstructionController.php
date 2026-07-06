<?php

namespace App\Http\Controllers\AssetMgmt;

use App\Http\Controllers\Controller;
use App\Models\AssetMgmt\AssetDeliveryInstruction;
use App\Models\AssetMgmt\AssetDiApproval;
use App\Models\Asset;
use App\Models\Site;
use Illuminate\Http\Request;

class AssetDeliveryInstructionController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(AssetDeliveryInstruction::class, 'di');
    }

    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $q = AssetDeliveryInstruction::query()
            ->with(['site', 'asset', 'requestedBy'])
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($request->filled('status'), fn($qq) => $qq->where('status', $request->status))
            ->when($request->filled('asset_id'), fn($qq) => $qq->where('asset_id', $request->asset_id))
            ->orderByDesc('created_at');

        $items = $q->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $assets = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))->orderBy('code')->get(['id','code','name']);
        $statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'finalized' => 'Finalized', 'cancelled' => 'Cancelled'];

        return view('admin.asset-mgmt.asset-delivery-instructions.index', compact('items','sites','assets','statuses','siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $assets = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))->orderBy('code')->get(['id','code','name']);
        $statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'finalized' => 'Finalized', 'cancelled' => 'Cancelled'];

        $assetDeliveryInstruction = new AssetDeliveryInstruction([
            'site_id'       => $siteId,
            'delivery_date' => now(),
            'status'        => 'draft',
        ]);

        return view('admin.asset-mgmt.asset-delivery-instructions.create', compact('assetDeliveryInstruction','sites','assets','statuses','siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id'       => 'required|uuid|exists:sites,id',
            'di_number'     => 'required|string|max:50|unique:asset_delivery_instructions,di_number',
            'asset_id'      => 'required|uuid|exists:assets,id',
            'delivery_date' => 'required|date',
            'from_location' => 'nullable|string|max:255',
            'to_location'   => 'required|string|max:255',
            'notes'         => 'nullable|string',
            'status'        => 'required|string|max:50',
        ]);

        $data['requested_by'] = $request->user()->id;

        AssetDeliveryInstruction::create($data);

        return redirect()
            ->route('asset_mgmt.asset_delivery_instructions.index', ['site' => $data['site_id']])
            ->with('success', 'Delivery Instruction tersimpan.');
    }

    public function edit(Request $request, AssetDeliveryInstruction $assetDeliveryInstruction)
    {
        $siteId = $assetDeliveryInstruction->site_id ?: ($request->query('site') ?: session('site_id'));

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $assets = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))->orderBy('code')->get(['id','code','name']);
        $statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'finalized' => 'Finalized', 'cancelled' => 'Cancelled'];

        return view('admin.asset-mgmt.asset-delivery-instructions.edit', compact('assetDeliveryInstruction','sites','assets','statuses','siteId'));
    }

    public function update(Request $request, AssetDeliveryInstruction $assetDeliveryInstruction)
    {
        $data = $request->validate([
            'site_id'       => 'required|uuid|exists:sites,id',
            'di_number'     => 'required|string|max:50|unique:asset_delivery_instructions,di_number,'.$assetDeliveryInstruction->id,
            'asset_id'      => 'required|uuid|exists:assets,id',
            'delivery_date' => 'required|date',
            'from_location' => 'nullable|string|max:255',
            'to_location'   => 'required|string|max:255',
            'notes'         => 'nullable|string',
            'status'        => 'required|string|max:50',
        ]);

        $assetDeliveryInstruction->update($data);

        return redirect()
            ->route('asset_mgmt.asset_delivery_instructions.index', ['site' => $data['site_id']])
            ->with('success', 'Delivery Instruction diperbarui.');
    }

    public function destroy(Request $request, AssetDeliveryInstruction $assetDeliveryInstruction)
    {
        $siteId = $assetDeliveryInstruction->site_id;
        $assetDeliveryInstruction->delete();

        return redirect()
            ->route('asset_mgmt.asset_delivery_instructions.index', ['site' => $siteId])
            ->with('success', 'Delivery Instruction dihapus.');
    }

    public function approve(Request $request, AssetDeliveryInstruction $assetDeliveryInstruction)
    {
        $data = $request->validate([
            'notes' => 'nullable|string',
        ]);

        AssetDiApproval::create([
            'di_id'       => $assetDeliveryInstruction->id,
            'approver_id' => $request->user()->id,
            'status'      => 'approved',
            'notes'       => $data['notes'] ?? null,
            'action_at'   => now(),
        ]);

        $assetDeliveryInstruction->update(['status' => 'approved']);

        return redirect()
            ->route('asset_mgmt.asset_delivery_instructions.index')
            ->with('success', 'Delivery Instruction disetujui.');
    }

    public function finalize(Request $request, AssetDeliveryInstruction $assetDeliveryInstruction)
    {
        $assetDeliveryInstruction->update(['status' => 'finalized']);

        return redirect()
            ->route('asset_mgmt.asset_delivery_instructions.index')
            ->with('success', 'Delivery Instruction difinalisasi.');
    }
}
