<?php

namespace App\Http\Controllers\Hse;

use App\Http\Controllers\Controller;
use App\Models\Hse\HazardArea;
use App\Models\Site;
use Illuminate\Http\Request;

class HazardAreaController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(HazardArea::class, 'hazardArea');
    }


    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $items = HazardArea::query()
            ->with('site')
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderBy('code')
            ->paginate(15)
            ->withQueryString();

        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        return view('admin.hse.hazard-areas.index', compact('items', 'sites', 'siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        $hazardArea = new HazardArea([
            'site_id' => $siteId,
            'is_active' => true,
        ]);

        return view('admin.hse.hazard-areas.create', compact('hazardArea', 'sites', 'siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'risk_level' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        HazardArea::create($data);

        return redirect()
            ->route('hse.hazard-areas.index', ['site' => $data['site_id']])
            ->with('success', 'Hazard Area tersimpan.');
    }

    public function edit(Request $request, HazardArea $hazardArea)
    {
        $siteId = $hazardArea->site_id ?: ($request->query('site') ?: session('site_id'));
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        return view('admin.hse.hazard-areas.edit', compact('hazardArea', 'sites', 'siteId'));
    }

    public function update(Request $request, HazardArea $hazardArea)
    {
        $data = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'code' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'risk_level' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $hazardArea->update($data);

        return redirect()
            ->route('hse.hazard-areas.index', ['site' => $data['site_id']])
            ->with('success', 'Hazard Area diperbarui.');
    }

    public function destroy(Request $request, HazardArea $hazardArea)
    {
        $siteId = $hazardArea->site_id;
        $hazardArea->delete();

        return redirect()
            ->route('hse.hazard-areas.index', ['site' => $siteId])
            ->with('success', 'Hazard Area dihapus.');
    }
}
