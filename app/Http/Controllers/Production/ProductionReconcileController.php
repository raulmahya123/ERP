<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\ProductionReconcile;
use App\Models\Site;
use Illuminate\Http\Request;

class ProductionReconcileController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ProductionReconcile::class, 'reconcile');
    }


    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $items = ProductionReconcile::query()
            ->with('site')
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderByDesc('reconcile_date')
            ->paginate(15)
            ->withQueryString();

        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        return view('admin.production.production-reconciles.index', compact('items', 'sites', 'siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        $reconcile = new ProductionReconcile([
            'site_id' => $siteId,
            'reconcile_date' => now(),
        ]);

        return view('admin.production.production-reconciles.create', compact('reconcile', 'sites', 'siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'reconcile_date' => 'required|date',
            'plan_volume' => 'nullable|numeric',
            'actual_volume' => 'nullable|numeric',
            'variance' => 'nullable|numeric',
            'variance_pct' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'reconciled_by' => 'nullable|exists:users,id',
        ]);

        ProductionReconcile::create($data);

        return redirect()
            ->route('production.production-reconciles.index', ['site' => $data['site_id']])
            ->with('success', 'Reconcile tersimpan.');
    }

    public function edit(Request $request, ProductionReconcile $productionReconcile)
    {
        $siteId = $productionReconcile->site_id ?: ($request->query('site') ?: session('site_id'));
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        return view('admin.production.production-reconciles.edit', compact('productionReconcile', 'sites', 'siteId'));
    }

    public function update(Request $request, ProductionReconcile $productionReconcile)
    {
        $data = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'reconcile_date' => 'required|date',
            'plan_volume' => 'nullable|numeric',
            'actual_volume' => 'nullable|numeric',
            'variance' => 'nullable|numeric',
            'variance_pct' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'reconciled_by' => 'nullable|exists:users,id',
        ]);

        $productionReconcile->update($data);

        return redirect()
            ->route('production.production-reconciles.index', ['site' => $data['site_id']])
            ->with('success', 'Reconcile diperbarui.');
    }

    public function destroy(Request $request, ProductionReconcile $productionReconcile)
    {
        $siteId = $productionReconcile->site_id;
        $productionReconcile->delete();

        return redirect()
            ->route('production.production-reconciles.index', ['site' => $siteId])
            ->with('success', 'Reconcile dihapus.');
    }
}
