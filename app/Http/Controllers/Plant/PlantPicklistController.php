<?php

namespace App\Http\Controllers\Plant;

use App\Http\Controllers\Controller;
use App\Models\Plant\PlantPicklist;
use App\Models\Plant\PlantWorkOrder;
use App\Models\Site;
use Illuminate\Http\Request;

class PlantPicklistController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(PlantPicklist::class, 'picklist');
    }

    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $q = PlantPicklist::query()
            ->with(['site', 'wo'])
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($request->filled('status'), fn($qq) => $qq->where('status', $request->status))
            ->when($request->filled('wo_id'), fn($qq) => $qq->where('wo_id', $request->wo_id))
            ->orderByDesc('created_at');

        $items = $q->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $workOrders = PlantWorkOrder::when($siteId, fn($qq) => $qq->where('site_id', $siteId))
                       ->orderBy('wo_number')->get(['id','wo_number']);
        $statuses = ['draft' => 'Draft', 'issued' => 'Issued', 'partial' => 'Partial', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];

        return view('admin.plant.plant-picklists.index', compact('items','sites','workOrders','statuses','siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $workOrders = PlantWorkOrder::when($siteId, fn($qq) => $qq->where('site_id', $siteId))
                       ->orderBy('wo_number')->get(['id','wo_number']);
        $statuses = ['draft' => 'Draft', 'issued' => 'Issued', 'partial' => 'Partial', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];

        $plantPicklist = new PlantPicklist([
            'site_id' => $siteId,
            'status'  => 'draft',
        ]);

        return view('admin.plant.plant-picklists.create', compact('plantPicklist','sites','workOrders','statuses','siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id'           => 'required|uuid|exists:sites,id',
            'wo_id'             => 'required|uuid|exists:plant_work_orders,id',
            'material_id'       => 'nullable|string|max:50',
            'quantity_required' => 'nullable|numeric|min:0',
            'quantity_issued'   => 'nullable|numeric|min:0',
            'uom'               => 'nullable|string|max:20',
            'notes'             => 'nullable|string',
            'status'            => 'required|string|max:50',
        ]);

        PlantPicklist::create($data);

        return redirect()
            ->route('plant.plant_picklists.index', ['site' => $data['site_id']])
            ->with('success', 'Picklist tersimpan.');
    }

    public function edit(Request $request, PlantPicklist $plantPicklist)
    {
        $siteId = $plantPicklist->site_id ?: ($request->query('site') ?: session('site_id'));

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $workOrders = PlantWorkOrder::when($siteId, fn($qq) => $qq->where('site_id', $siteId))
                       ->orderBy('wo_number')->get(['id','wo_number']);
        $statuses = ['draft' => 'Draft', 'issued' => 'Issued', 'partial' => 'Partial', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];

        return view('admin.plant.plant-picklists.edit', compact('plantPicklist','sites','workOrders','statuses','siteId'));
    }

    public function update(Request $request, PlantPicklist $plantPicklist)
    {
        $data = $request->validate([
            'site_id'           => 'required|uuid|exists:sites,id',
            'wo_id'             => 'required|uuid|exists:plant_work_orders,id',
            'material_id'       => 'nullable|string|max:50',
            'quantity_required' => 'nullable|numeric|min:0',
            'quantity_issued'   => 'nullable|numeric|min:0',
            'uom'               => 'nullable|string|max:20',
            'notes'             => 'nullable|string',
            'status'            => 'required|string|max:50',
        ]);

        $plantPicklist->update($data);

        return redirect()
            ->route('plant.plant_picklists.index', ['site' => $data['site_id']])
            ->with('success', 'Picklist diperbarui.');
    }

    public function destroy(Request $request, PlantPicklist $plantPicklist)
    {
        $siteId = $plantPicklist->site_id;
        $plantPicklist->delete();

        return redirect()
            ->route('plant.plant_picklists.index', ['site' => $siteId])
            ->with('success', 'Picklist dihapus.');
    }
}
