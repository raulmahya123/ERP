<?php

namespace App\Http\Controllers\Scm;

use App\Http\Controllers\Controller;
use App\Models\Scm\VendorEvaluation;
use App\Models\Scm\VendorEvaluationApproval;
use App\Models\Site;
use App\Models\MasterRecord;
use Illuminate\Http\Request;

class VendorEvaluationController extends Controller
{
    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $q = VendorEvaluation::with(['site', 'vendor'])
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($request->filled('vendor_id'), fn($qq) => $qq->where('vendor_id', $request->vendor_id))
            ->when($request->filled('evaluation_period'), fn($qq) => $qq->where('evaluation_period', 'like', '%' . $request->evaluation_period . '%'))
            ->when($request->filled('status'), fn($qq) => $qq->where('status', $request->status))
            ->orderByDesc('created_at');

        $items = $q->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $vendors = MasterRecord::orderBy('name')->get(['id', 'code', 'name']);

        return view('admin.scm.vendor-evaluations.index', compact('items', 'sites', 'vendors', 'siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $vendors = MasterRecord::orderBy('name')->get(['id', 'code', 'name']);

        $vendorEvaluation = new VendorEvaluation([
            'site_id' => $siteId,
            'status' => 'draft',
        ]);

        return view('admin.scm.vendor-evaluations.create', compact(
            'vendorEvaluation', 'sites', 'vendors', 'siteId'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id' => 'required|string',
            'vendor_id' => 'required|string',
            'evaluation_period' => 'nullable|string|max:50',
            'quality_score' => 'nullable|numeric',
            'delivery_score' => 'nullable|numeric',
            'price_score' => 'nullable|numeric',
            'service_score' => 'nullable|numeric',
            'total_score' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        VendorEvaluation::create($data);

        return redirect()
            ->route('scm.vendor_evaluations.index', ['site' => $data['site_id']])
            ->with('success', 'Vendor Evaluation tersimpan.');
    }

    public function edit(Request $request, VendorEvaluation $vendor_evaluation)
    {
        $siteId = $vendor_evaluation->site_id ?: ($request->query('site') ?: session('site_id'));
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        $vendors = MasterRecord::orderBy('name')->get(['id', 'code', 'name']);

        return view('admin.scm.vendor-evaluations.edit', compact(
            'vendor_evaluation', 'sites', 'vendors', 'siteId'
        ));
    }

    public function update(Request $request, VendorEvaluation $vendor_evaluation)
    {
        $data = $request->validate([
            'site_id' => 'required|string',
            'vendor_id' => 'required|string',
            'evaluation_period' => 'nullable|string|max:50',
            'quality_score' => 'nullable|numeric',
            'delivery_score' => 'nullable|numeric',
            'price_score' => 'nullable|numeric',
            'service_score' => 'nullable|numeric',
            'total_score' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'status' => 'nullable|string',
        ]);

        $vendor_evaluation->update($data);

        return redirect()
            ->route('scm.vendor_evaluations.index', ['site' => $data['site_id']])
            ->with('success', 'Vendor Evaluation diperbarui.');
    }

    public function destroy(Request $request, VendorEvaluation $vendor_evaluation)
    {
        $siteId = $vendor_evaluation->site_id;
        $vendor_evaluation->delete();

        return redirect()
            ->route('scm.vendor_evaluations.index', ['site' => $siteId])
            ->with('success', 'Vendor Evaluation dihapus.');
    }

    public function approve(Request $request, VendorEvaluation $vendor_evaluation)
    {
        $vendor_evaluation->update([
            'status' => 'approved',
        ]);

        VendorEvaluationApproval::create([
            'evaluation_id' => $vendor_evaluation->id,
            'approver_id' => $request->user()->id,
            'status' => 'approved',
            'notes' => $request->input('notes'),
            'action_at' => now(),
        ]);

        return redirect()
            ->route('scm.vendor_evaluations.index', ['site' => $vendor_evaluation->site_id])
            ->with('success', 'Vendor Evaluation disetujui.');
    }
}
