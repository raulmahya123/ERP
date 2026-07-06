<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\ProductionShiftClosing;
use App\Models\Site;
use Illuminate\Http\Request;

class ProductionShiftClosingController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ProductionShiftClosing::class, 'closing');
    }


    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $items = ProductionShiftClosing::query()
            ->with('site')
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderByDesc('close_date')
            ->orderBy('shift')
            ->paginate(15)
            ->withQueryString();

        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        return view('admin.production.production-shift-closings.index', compact('items', 'sites', 'siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        $closing = new ProductionShiftClosing([
            'site_id' => $siteId,
            'close_date' => now(),
            'is_unlocked' => false,
        ]);

        return view('admin.production.production-shift-closings.create', compact('closing', 'sites', 'siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'close_date' => 'required|date',
            'shift' => 'nullable|string|max:20',
            'closed_at' => 'nullable|date',
            'is_unlocked' => 'boolean',
            'closed_by' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        ProductionShiftClosing::create($data);

        return redirect()
            ->route('production.production-shift-closings.index', ['site' => $data['site_id']])
            ->with('success', 'Shift Closing tersimpan.');
    }

    public function edit(Request $request, ProductionShiftClosing $productionShiftClosing)
    {
        $siteId = $productionShiftClosing->site_id ?: ($request->query('site') ?: session('site_id'));
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        return view('admin.production.production-shift-closings.edit', compact('productionShiftClosing', 'sites', 'siteId'));
    }

    public function update(Request $request, ProductionShiftClosing $productionShiftClosing)
    {
        $data = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'close_date' => 'required|date',
            'shift' => 'nullable|string|max:20',
            'closed_at' => 'nullable|date',
            'is_unlocked' => 'boolean',
            'closed_by' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);

        $productionShiftClosing->update($data);

        return redirect()
            ->route('production.production-shift-closings.index', ['site' => $data['site_id']])
            ->with('success', 'Shift Closing diperbarui.');
    }

    public function destroy(Request $request, ProductionShiftClosing $productionShiftClosing)
    {
        $siteId = $productionShiftClosing->site_id;
        $productionShiftClosing->delete();

        return redirect()
            ->route('production.production-shift-closings.index', ['site' => $siteId])
            ->with('success', 'Shift Closing dihapus.');
    }

    public function unlock(Request $request, ProductionShiftClosing $productionShiftClosing)
    {
        $productionShiftClosing->update([
            'is_unlocked' => true,
            'unlocked_at' => now(),
            'unlocked_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('production.production-shift-closings.index', ['site' => $productionShiftClosing->site_id])
            ->with('success', 'Shift Closing berhasil di-unlock.');
    }
}
