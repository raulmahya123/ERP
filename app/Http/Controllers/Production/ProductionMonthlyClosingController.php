<?php

namespace App\Http\Controllers\Production;

use App\Http\Controllers\Controller;
use App\Models\Production\ProductionMonthlyClosing;
use App\Models\Site;
use Illuminate\Http\Request;

class ProductionMonthlyClosingController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(ProductionMonthlyClosing::class, 'closing');
    }


    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $items = ProductionMonthlyClosing::query()
            ->with('site')
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderByDesc('year')
            ->orderByDesc('month')
            ->paginate(15)
            ->withQueryString();

        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        return view('admin.production.production-monthly-closings.index', compact('items', 'sites', 'siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        $closing = new ProductionMonthlyClosing([
            'site_id' => $siteId,
            'is_unlocked' => false,
        ]);

        return view('admin.production.production-monthly-closings.create', compact('closing', 'sites', 'siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'year' => 'required|integer|min:2000|max:2099',
            'month' => 'required|integer|min:1|max:12',
            'closed_at' => 'nullable|date',
            'is_unlocked' => 'boolean',
            'closed_by' => 'nullable|exists:users,id',
        ]);

        ProductionMonthlyClosing::create($data);

        return redirect()
            ->route('production.production-monthly-closings.index', ['site' => $data['site_id']])
            ->with('success', 'Monthly Closing tersimpan.');
    }

    public function edit(Request $request, ProductionMonthlyClosing $productionMonthlyClosing)
    {
        $siteId = $productionMonthlyClosing->site_id ?: ($request->query('site') ?: session('site_id'));
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        return view('admin.production.production-monthly-closings.edit', compact('productionMonthlyClosing', 'sites', 'siteId'));
    }

    public function update(Request $request, ProductionMonthlyClosing $productionMonthlyClosing)
    {
        $data = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'year' => 'required|integer|min:2000|max:2099',
            'month' => 'required|integer|min:1|max:12',
            'closed_at' => 'nullable|date',
            'is_unlocked' => 'boolean',
            'closed_by' => 'nullable|exists:users,id',
        ]);

        $productionMonthlyClosing->update($data);

        return redirect()
            ->route('production.production-monthly-closings.index', ['site' => $data['site_id']])
            ->with('success', 'Monthly Closing diperbarui.');
    }

    public function destroy(Request $request, ProductionMonthlyClosing $productionMonthlyClosing)
    {
        $siteId = $productionMonthlyClosing->site_id;
        $productionMonthlyClosing->delete();

        return redirect()
            ->route('production.production-monthly-closings.index', ['site' => $siteId])
            ->with('success', 'Monthly Closing dihapus.');
    }

    public function unlock(Request $request, ProductionMonthlyClosing $productionMonthlyClosing)
    {
        $productionMonthlyClosing->update([
            'is_unlocked' => true,
            'unlocked_at' => now(),
            'unlocked_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('production.production-monthly-closings.index', ['site' => $productionMonthlyClosing->site_id])
            ->with('success', 'Monthly Closing berhasil di-unlock.');
    }
}
