<?php

namespace App\Http\Controllers\Hse;

use App\Http\Controllers\Controller;
use App\Models\Hse\HseInspectionReport;
use App\Models\Site;
use Illuminate\Http\Request;

class HseInspectionReportController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(HseInspectionReport::class, 'inspectionReport');
    }


    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $items = HseInspectionReport::query()
            ->with('site')
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderByDesc('inspection_date')
            ->paginate(15)
            ->withQueryString();

        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        return view('admin.hse.hse-inspection-reports.index', compact('items', 'sites', 'siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        $inspectionReport = new HseInspectionReport([
            'site_id' => $siteId,
            'inspection_date' => now(),
            'status' => 'draft',
        ]);

        return view('admin.hse.hse-inspection-reports.create', compact('inspectionReport', 'sites', 'siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'report_number' => 'required|string|max:50',
            'inspection_type' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'inspection_date' => 'nullable|date',
            'findings' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'status' => 'nullable|string|max:50',
            'inspector_id' => 'nullable|exists:users,id',
            'verified_by' => 'nullable|exists:users,id',
            'verified_at' => 'nullable|date',
        ]);

        HseInspectionReport::create($data);

        return redirect()
            ->route('hse.hse-inspection-reports.index', ['site' => $data['site_id']])
            ->with('success', 'Inspection Report tersimpan.');
    }

    public function edit(Request $request, HseInspectionReport $hseInspectionReport)
    {
        $siteId = $hseInspectionReport->site_id ?: ($request->query('site') ?: session('site_id'));
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        return view('admin.hse.hse-inspection-reports.edit', compact('hseInspectionReport', 'sites', 'siteId'));
    }

    public function update(Request $request, HseInspectionReport $hseInspectionReport)
    {
        $data = $request->validate([
            'site_id' => 'required|exists:sites,id',
            'report_number' => 'required|string|max:50',
            'inspection_type' => 'nullable|string|max:100',
            'location' => 'nullable|string|max:255',
            'inspection_date' => 'nullable|date',
            'findings' => 'nullable|string',
            'recommendations' => 'nullable|string',
            'status' => 'nullable|string|max:50',
            'inspector_id' => 'nullable|exists:users,id',
            'verified_by' => 'nullable|exists:users,id',
            'verified_at' => 'nullable|date',
        ]);

        $hseInspectionReport->update($data);

        return redirect()
            ->route('hse.hse-inspection-reports.index', ['site' => $data['site_id']])
            ->with('success', 'Inspection Report diperbarui.');
    }

    public function destroy(Request $request, HseInspectionReport $hseInspectionReport)
    {
        $siteId = $hseInspectionReport->site_id;
        $hseInspectionReport->delete();

        return redirect()
            ->route('hse.hse-inspection-reports.index', ['site' => $siteId])
            ->with('success', 'Inspection Report dihapus.');
    }
}
