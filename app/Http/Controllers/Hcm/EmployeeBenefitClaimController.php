<?php

namespace App\Http\Controllers\Hcm;

use App\Http\Controllers\Controller;
use App\Models\Hcm\EmployeeBenefitClaim;
use App\Models\Hcm\EmployeeBenefit;
use App\Models\User;
use Illuminate\Http\Request;

class EmployeeBenefitClaimController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(EmployeeBenefitClaim::class, 'claim');
    }

    public function index(Request $request)
    {
        $q = EmployeeBenefitClaim::query()
            ->with(['employee', 'benefit'])
            ->when($request->filled('status'), fn($qq) => $qq->where('status', $request->status))
            ->when($request->filled('benefit_id'), fn($qq) => $qq->where('benefit_id', $request->benefit_id))
            ->when($request->filled('employee_id'), fn($qq) => $qq->where('employee_id', $request->employee_id))
            ->when($request->filled('from'), fn($qq) => $qq->where('claim_date','>=',$request->from))
            ->when($request->filled('to'), fn($qq) => $qq->where('claim_date','<=',$request->to))
            ->orderByDesc('created_at');

        $items = $q->paginate(15)->withQueryString();

        $employees = User::orderBy('name')->get(['id','name']);
        $benefits = EmployeeBenefit::orderBy('benefit_name')->get(['id','benefit_code','benefit_name']);
        $statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'paid' => 'Paid'];

        return view('admin.hcm.employee-benefit-claims.index', compact('items','employees','benefits','statuses'));
    }

    public function create()
    {
        $employees = User::orderBy('name')->get(['id','name']);
        $benefits = EmployeeBenefit::where('is_active', true)->orderBy('benefit_name')->get(['id','benefit_code','benefit_name']);
        $statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'paid' => 'Paid'];

        $employeeBenefitClaim = new EmployeeBenefitClaim(['status' => 'draft']);

        return view('admin.hcm.employee-benefit-claims.create', compact('employeeBenefitClaim','employees','benefits','statuses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|uuid|exists:users,id',
            'benefit_id'  => 'required|uuid|exists:employee_benefits,id',
            'claim_date'  => 'required|date',
            'amount'      => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status'      => 'required|string|max:50',
        ]);

        EmployeeBenefitClaim::create($data);

        return redirect()
            ->route('hcm.employee_benefit_claims.index')
            ->with('success', 'Klaim Benefit tersimpan.');
    }

    public function edit(Request $request, EmployeeBenefitClaim $employeeBenefitClaim)
    {
        $employees = User::orderBy('name')->get(['id','name']);
        $benefits = EmployeeBenefit::where('is_active', true)->orderBy('benefit_name')->get(['id','benefit_code','benefit_name']);
        $statuses = ['draft' => 'Draft', 'submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected', 'paid' => 'Paid'];

        return view('admin.hcm.employee-benefit-claims.edit', compact('employeeBenefitClaim','employees','benefits','statuses'));
    }

    public function update(Request $request, EmployeeBenefitClaim $employeeBenefitClaim)
    {
        $data = $request->validate([
            'employee_id' => 'required|uuid|exists:users,id',
            'benefit_id'  => 'required|uuid|exists:employee_benefits,id',
            'claim_date'  => 'required|date',
            'amount'      => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status'      => 'required|string|max:50',
        ]);

        $employeeBenefitClaim->update($data);

        return redirect()
            ->route('hcm.employee_benefit_claims.index')
            ->with('success', 'Klaim Benefit diperbarui.');
    }

    public function destroy(Request $request, EmployeeBenefitClaim $employeeBenefitClaim)
    {
        $employeeBenefitClaim->delete();

        return redirect()
            ->route('hcm.employee_benefit_claims.index')
            ->with('success', 'Klaim Benefit dihapus.');
    }
}
