<?php

namespace App\Http\Controllers\Plant;

use App\Http\Controllers\Controller;
use App\Models\Plant\PlantWorkOrder;
use App\Models\Plant\PlantWoApproval;
use App\Models\Asset;
use App\Models\Site;
use App\Models\User;
use Illuminate\Http\Request;

class PlantWorkOrderController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(PlantWorkOrder::class, 'workOrder');
    }

    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $q = PlantWorkOrder::query()
            ->with(['site', 'asset', 'requestedBy', 'assignedTo'])
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($request->filled('status'), fn($qq) => $qq->where('status', $request->status))
            ->when($request->filled('wo_type'), fn($qq) => $qq->where('wo_type', $request->wo_type))
            ->when($request->filled('priority'), fn($qq) => $qq->where('priority', $request->priority))
            ->when($request->filled('asset_id'), fn($qq) => $qq->where('asset_id', $request->asset_id))
            ->when($request->filled('assigned_to'), fn($qq) => $qq->where('assigned_to', $request->assigned_to))
            ->orderByDesc('created_at');

        $items = $q->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $assets = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))->orderBy('code')->get(['id','code','name']);
        $users = User::orderBy('name')->get(['id','name']);
        $woTypes = ['preventive' => 'Preventive', 'corrective' => 'Corrective', 'predictive' => 'Predictive', 'breakdown' => 'Breakdown', 'emergency' => 'Emergency'];
        $priorities = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'];
        $statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled'];

        return view('admin.plant.plant-work-orders.index', compact('items','sites','assets','users','woTypes','priorities','statuses','siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $assets = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))->orderBy('code')->get(['id','code','name']);
        $users = User::orderBy('name')->get(['id','name']);
        $woTypes = ['preventive' => 'Preventive', 'corrective' => 'Corrective', 'predictive' => 'Predictive', 'breakdown' => 'Breakdown', 'emergency' => 'Emergency'];
        $priorities = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'];
        $statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled'];

        $plantWorkOrder = new PlantWorkOrder([
            'site_id'      => $siteId,
            'planned_start' => now(),
            'planned_end'   => now()->addDays(7),
            'status'        => 'draft',
        ]);

        return view('admin.plant.plant-work-orders.create', compact('plantWorkOrder','sites','assets','users','woTypes','priorities','statuses','siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id'       => 'required|uuid|exists:sites,id',
            'wo_number'     => 'required|string|max:50|unique:plant_work_orders,wo_number',
            'asset_id'      => 'required|uuid|exists:assets,id',
            'wo_type'       => 'required|string|max:50',
            'priority'      => 'required|string|max:50',
            'description'   => 'nullable|string',
            'planned_start' => 'required|date',
            'planned_end'   => 'required|date|after_or_equal:planned_start',
            'actual_start'  => 'nullable|date',
            'actual_end'    => 'nullable|date|after_or_equal:actual_start',
            'status'        => 'required|string|max:50',
            'notes'         => 'nullable|string',
            'assigned_to'   => 'nullable|uuid|exists:users,id',
        ]);

        $data['requested_by'] = $request->user()->id;

        PlantWorkOrder::create($data);

        return redirect()
            ->route('plant.plant_work_orders.index', ['site' => $data['site_id']])
            ->with('success', 'Work Order tersimpan.');
    }

    public function edit(Request $request, PlantWorkOrder $plantWorkOrder)
    {
        $siteId = $plantWorkOrder->site_id ?: ($request->query('site') ?: session('site_id'));

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $assets = Asset::when($siteId, fn($qq) => $qq->where('site_id', $siteId))->orderBy('code')->get(['id','code','name']);
        $users = User::orderBy('name')->get(['id','name']);
        $woTypes = ['preventive' => 'Preventive', 'corrective' => 'Corrective', 'predictive' => 'Predictive', 'breakdown' => 'Breakdown', 'emergency' => 'Emergency'];
        $priorities = ['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'];
        $statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'rejected' => 'Rejected', 'cancelled' => 'Cancelled'];

        return view('admin.plant.plant-work-orders.edit', compact('plantWorkOrder','sites','assets','users','woTypes','priorities','statuses','siteId'));
    }

    public function update(Request $request, PlantWorkOrder $plantWorkOrder)
    {
        $data = $request->validate([
            'site_id'       => 'required|uuid|exists:sites,id',
            'wo_number'     => 'required|string|max:50|unique:plant_work_orders,wo_number,'.$plantWorkOrder->id,
            'asset_id'      => 'required|uuid|exists:assets,id',
            'wo_type'       => 'required|string|max:50',
            'priority'      => 'required|string|max:50',
            'description'   => 'nullable|string',
            'planned_start' => 'required|date',
            'planned_end'   => 'required|date|after_or_equal:planned_start',
            'actual_start'  => 'nullable|date',
            'actual_end'    => 'nullable|date|after_or_equal:actual_start',
            'status'        => 'required|string|max:50',
            'notes'         => 'nullable|string',
            'assigned_to'   => 'nullable|uuid|exists:users,id',
        ]);

        $plantWorkOrder->update($data);

        return redirect()
            ->route('plant.plant_work_orders.index', ['site' => $data['site_id']])
            ->with('success', 'Work Order diperbarui.');
    }

    public function destroy(Request $request, PlantWorkOrder $plantWorkOrder)
    {
        $siteId = $plantWorkOrder->site_id;
        $plantWorkOrder->delete();

        return redirect()
            ->route('plant.plant_work_orders.index', ['site' => $siteId])
            ->with('success', 'Work Order dihapus.');
    }

    public function approve(Request $request, PlantWorkOrder $plantWorkOrder)
    {
        $data = $request->validate([
            'notes' => 'nullable|string',
        ]);

        PlantWoApproval::create([
            'wo_id'           => $plantWorkOrder->id,
            'approver_id'     => $request->user()->id,
            'approval_level'  => 1,
            'status'          => 'approved',
            'notes'           => $data['notes'] ?? null,
            'action_at'       => now(),
        ]);

        $plantWorkOrder->update([
            'status'      => 'approved',
            'approved_by' => $request->user()->id,
            'approved_at' => now(),
        ]);

        return redirect()
            ->route('plant.plant_work_orders.index')
            ->with('success', 'Work Order disetujui.');
    }

    public function process(Request $request, PlantWorkOrder $plantWorkOrder)
    {
        $data = $request->validate([
            'actual_start' => 'nullable|date',
            'actual_end'   => 'nullable|date',
            'notes'        => 'nullable|string',
        ]);

        $updateData = ['status' => 'in_progress'];

        if ($data['actual_start']) {
            $updateData['actual_start'] = $data['actual_start'];
        }

        if ($data['actual_end']) {
            $updateData['actual_end'] = $data['actual_end'];
            $updateData['status'] = 'completed';
        }

        if ($data['notes']) {
            $updateData['notes'] = $data['notes'];
        }

        $plantWorkOrder->update($updateData);

        $message = $updateData['status'] === 'completed' ? 'Work Order diselesaikan.' : 'Work Order diproses.';

        return redirect()
            ->route('plant.plant_work_orders.index')
            ->with('success', $message);
    }
}
