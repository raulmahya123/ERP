<?php

namespace App\Http\Controllers\Hcm;

use App\Http\Controllers\Controller;
use App\Models\Hcm\RecruitmentCandidate;
use Illuminate\Http\Request;

class RecruitmentCandidateController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(RecruitmentCandidate::class, 'candidate');
    }

    public function index(Request $request)
    {
        $q = RecruitmentCandidate::query()
            ->with('createdBy')
            ->when($request->filled('search'), fn($qq) => $qq->where(function($q) use ($request) {
                $q->where('full_name', 'like', '%'.$request->search.'%')
                  ->orWhere('candidate_number', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%')
                  ->orWhere('phone', 'like', '%'.$request->search.'%');
            }))
            ->when($request->filled('status'), fn($qq) => $qq->where('status', $request->status))
            ->when($request->filled('position_applied'), fn($qq) => $qq->where('position_applied', 'like', '%'.$request->position_applied.'%'))
            ->orderByDesc('created_at');

        $items = $q->paginate(15)->withQueryString();

        $statuses = ['new' => 'New', 'reviewed' => 'Reviewed', 'shortlisted' => 'Shortlisted', 'interviewed' => 'Interviewed', 'offered' => 'Offered', 'hired' => 'Hired', 'rejected' => 'Rejected'];

        return view('admin.hcm.recruitment-candidates.index', compact('items', 'statuses'));
    }

    public function create()
    {
        $statuses = ['new' => 'New', 'reviewed' => 'Reviewed', 'shortlisted' => 'Shortlisted', 'interviewed' => 'Interviewed', 'offered' => 'Offered', 'hired' => 'Hired', 'rejected' => 'Rejected'];

        $recruitmentCandidate = new RecruitmentCandidate(['status' => 'new']);

        return view('admin.hcm.recruitment-candidates.create', compact('recruitmentCandidate', 'statuses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'candidate_number' => 'required|string|max:50|unique:recruitment_candidates,candidate_number',
            'full_name'        => 'required|string|max:255',
            'email'            => 'required|email|max:255|unique:recruitment_candidates,email',
            'phone'            => 'nullable|string|max:50',
            'position_applied' => 'required|string|max:255',
            'address'          => 'nullable|string',
            'education'        => 'nullable|string|max:255',
            'experience'       => 'nullable|string',
            'status'           => 'required|string|max:50',
            'notes'            => 'nullable|string',
        ]);

        $data['created_by'] = $request->user()->id;

        RecruitmentCandidate::create($data);

        return redirect()
            ->route('hcm.recruitment_candidates.index')
            ->with('success', 'Kandidat tersimpan.');
    }

    public function edit(Request $request, RecruitmentCandidate $recruitmentCandidate)
    {
        $statuses = ['new' => 'New', 'reviewed' => 'Reviewed', 'shortlisted' => 'Shortlisted', 'interviewed' => 'Interviewed', 'offered' => 'Offered', 'hired' => 'Hired', 'rejected' => 'Rejected'];

        return view('admin.hcm.recruitment-candidates.edit', compact('recruitmentCandidate', 'statuses'));
    }

    public function update(Request $request, RecruitmentCandidate $recruitmentCandidate)
    {
        $data = $request->validate([
            'candidate_number' => 'required|string|max:50|unique:recruitment_candidates,candidate_number,'.$recruitmentCandidate->id,
            'full_name'        => 'required|string|max:255',
            'email'            => 'required|email|max:255|unique:recruitment_candidates,email,'.$recruitmentCandidate->id,
            'phone'            => 'nullable|string|max:50',
            'position_applied' => 'required|string|max:255',
            'address'          => 'nullable|string',
            'education'        => 'nullable|string|max:255',
            'experience'       => 'nullable|string',
            'status'           => 'required|string|max:50',
            'notes'            => 'nullable|string',
        ]);

        $recruitmentCandidate->update($data);

        return redirect()
            ->route('hcm.recruitment_candidates.index')
            ->with('success', 'Kandidat diperbarui.');
    }

    public function destroy(Request $request, RecruitmentCandidate $recruitmentCandidate)
    {
        $recruitmentCandidate->delete();

        return redirect()
            ->route('hcm.recruitment_candidates.index')
            ->with('success', 'Kandidat dihapus.');
    }
}
