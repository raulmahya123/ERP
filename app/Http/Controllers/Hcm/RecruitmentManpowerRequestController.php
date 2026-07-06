<?php

namespace App\Http\Controllers\Hcm;

use App\Http\Controllers\Controller;
use App\Models\Hcm\RecruitmentManpowerRequest;
use App\Models\Site;
use Illuminate\Http\Request;

class RecruitmentManpowerRequestController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(RecruitmentManpowerRequest::class, 'manpowerRequest');
    }

    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $q = RecruitmentManpowerRequest::query()
            ->with(['site', 'requestedBy'])
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($request->filled('status'), fn($qq) => $qq->where('status', $request->status))
            ->when($request->filled('position'), fn($qq) => $qq->where('position', 'like', '%'.$request->position.'%'))
            ->orderByDesc('created_at');

        $items = $q->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'fulfilled' => 'Fulfilled', 'cancelled' => 'Cancelled'];

        return view('admin.hcm.recruitment-manpower-requests.index', compact('items','sites','statuses','siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'fulfilled' => 'Fulfilled', 'cancelled' => 'Cancelled'];

        $recruitmentManpowerRequest = new RecruitmentManpowerRequest([
            'site_id' => $siteId,
            'status'  => 'draft',
        ]);

        return view('admin.hcm.recruitment-manpower-requests.create', compact('recruitmentManpowerRequest','sites','statuses','siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id'        => 'required|uuid|exists:sites,id',
            'request_number' => 'required|string|max:50|unique:recruitment_manpower_requests,request_number',
            'position'       => 'required|string|max:255',
            'quantity'       => 'required|integer|min:1',
            'required_date'  => 'required|date',
            'justification'  => 'nullable|string',
            'status'         => 'required|string|max:50',
        ]);

        $data['requested_by'] = $request->user()->id;

        RecruitmentManpowerRequest::create($data);

        return redirect()
            ->route('hcm.recruitment_manpower_requests.index', ['site' => $data['site_id']])
            ->with('success', 'Manpower Request tersimpan.');
    }

    public function edit(Request $request, RecruitmentManpowerRequest $recruitmentManpowerRequest)
    {
        $siteId = $recruitmentManpowerRequest->site_id ?: ($request->query('site') ?: session('site_id'));

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'fulfilled' => 'Fulfilled', 'cancelled' => 'Cancelled'];

        return view('admin.hcm.recruitment-manpower-requests.edit', compact('recruitmentManpowerRequest','sites','statuses','siteId'));
    }

    public function update(Request $request, RecruitmentManpowerRequest $recruitmentManpowerRequest)
    {
        $data = $request->validate([
            'site_id'        => 'required|uuid|exists:sites,id',
            'request_number' => 'required|string|max:50|unique:recruitment_manpower_requests,request_number,'.$recruitmentManpowerRequest->id,
            'position'       => 'required|string|max:255',
            'quantity'       => 'required|integer|min:1',
            'required_date'  => 'required|date',
            'justification'  => 'nullable|string',
            'status'         => 'required|string|max:50',
        ]);

        $recruitmentManpowerRequest->update($data);

        return redirect()
            ->route('hcm.recruitment_manpower_requests.index', ['site' => $data['site_id']])
            ->with('success', 'Manpower Request diperbarui.');
    }

    public function destroy(Request $request, RecruitmentManpowerRequest $recruitmentManpowerRequest)
    {
        $siteId = $recruitmentManpowerRequest->site_id;
        $recruitmentManpowerRequest->delete();

        return redirect()
            ->route('hcm.recruitment_manpower_requests.index', ['site' => $siteId])
            ->with('success', 'Manpower Request dihapus.');
    }
}
