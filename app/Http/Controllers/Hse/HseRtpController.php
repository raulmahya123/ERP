<?php

namespace App\Http\Controllers\Hse;

use App\Http\Controllers\Controller;
use App\Models\Hse\HseRtp;
use App\Models\Site;
use Illuminate\Http\Request;

class HseRtpController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(HseRtp::class, 'rtp');
    }


    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $items = HseRtp::query()
            ->with('site')
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        return view('admin.hse.hse-rtp.index', compact('items', 'sites', 'siteId'));
    }

    public function show(Request $request, HseRtp $hseRtp)
    {
        $siteId = $hseRtp->site_id ?: ($request->query('site') ?: session('site_id'));
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);
        return view('admin.hse.hse-rtp.show', compact('hseRtp', 'sites', 'siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        $hseRtp = new HseRtp([
            'site_id' => $siteId,
            'status' => 'open',
        ]);

        return view('admin.hse.hse-rtp.create', compact('hseRtp', 'sites', 'siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'hazard_report_id' => 'nullable|exists:hazard_reports,id',
            'site_id' => 'required|exists:sites,id',
            'rtp_number' => 'required|string|max:50',
            'corrective_action' => 'nullable|string',
            'preventive_action' => 'nullable|string',
            'pic' => 'nullable|string|max:255',
            'target_date' => 'nullable|date',
            'completion_date' => 'nullable|date',
            'status' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'created_by' => 'nullable|exists:users,id',
        ]);

        HseRtp::create($data);

        return redirect()
            ->route('hse.hse-rtp.index', ['site' => $data['site_id']])
            ->with('success', 'RTP tersimpan.');
    }

    public function edit(Request $request, HseRtp $hseRtp)
    {
        $siteId = $hseRtp->site_id ?: ($request->query('site') ?: session('site_id'));
        $sites = Site::orderBy('code')->get(['id', 'code', 'name']);

        return view('admin.hse.hse-rtp.edit', compact('hseRtp', 'sites', 'siteId'));
    }

    public function update(Request $request, HseRtp $hseRtp)
    {
        $data = $request->validate([
            'hazard_report_id' => 'nullable|exists:hazard_reports,id',
            'site_id' => 'required|exists:sites,id',
            'rtp_number' => 'required|string|max:50',
            'corrective_action' => 'nullable|string',
            'preventive_action' => 'nullable|string',
            'pic' => 'nullable|string|max:255',
            'target_date' => 'nullable|date',
            'completion_date' => 'nullable|date',
            'status' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
            'created_by' => 'nullable|exists:users,id',
        ]);

        $hseRtp->update($data);

        return redirect()
            ->route('hse.hse-rtp.index', ['site' => $data['site_id']])
            ->with('success', 'RTP diperbarui.');
    }

    public function destroy(Request $request, HseRtp $hseRtp)
    {
        $siteId = $hseRtp->site_id;
        $hseRtp->delete();

        return redirect()
            ->route('hse.hse-rtp.index', ['site' => $siteId])
            ->with('success', 'RTP dihapus.');
    }
}
