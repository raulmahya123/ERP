<?php

namespace App\Http\Controllers\Hcm;

use App\Http\Controllers\Controller;
use App\Models\Hcm\EmployeeBenefit;
use App\Models\Site;
use Illuminate\Http\Request;

class EmployeeBenefitController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(EmployeeBenefit::class, 'benefit');
    }

    public function index(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $q = EmployeeBenefit::query()
            ->with('site')
            ->when($siteId, fn($qq) => $qq->where('site_id', $siteId))
            ->when($request->filled('benefit_type'), fn($qq) => $qq->where('benefit_type', $request->benefit_type))
            ->when($request->filled('is_active'), fn($qq) => $qq->where('is_active', $request->boolean('is_active')))
            ->orderBy('benefit_code');

        $items = $q->paginate(15)->withQueryString();

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $benefitTypes = ['allowance' => 'Allowance', 'insurance' => 'Insurance', 'leave' => 'Leave', 'bonus' => 'Bonus', 'facility' => 'Facility', 'other' => 'Other'];

        return view('admin.hcm.employee-benefits.index', compact('items','sites','benefitTypes','siteId'));
    }

    public function create(Request $request)
    {
        $siteId = $request->query('site') ?: session('site_id');

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $benefitTypes = ['allowance' => 'Allowance', 'insurance' => 'Insurance', 'leave' => 'Leave', 'bonus' => 'Bonus', 'facility' => 'Facility', 'other' => 'Other'];

        $employeeBenefit = new EmployeeBenefit([
            'site_id'   => $siteId,
            'is_active' => true,
        ]);

        return view('admin.hcm.employee-benefits.create', compact('employeeBenefit','sites','benefitTypes','siteId'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'site_id'      => 'required|uuid|exists:sites,id',
            'benefit_code' => 'required|string|max:50|unique:employee_benefits,benefit_code',
            'benefit_name' => 'required|string|max:255',
            'benefit_type' => 'required|string|max:50',
            'amount'       => 'nullable|numeric|min:0',
            'description'  => 'nullable|string',
            'is_active'    => 'boolean',
        ]);

        EmployeeBenefit::create($data);

        return redirect()
            ->route('hcm.employee_benefits.index', ['site' => $data['site_id']])
            ->with('success', 'Benefit tersimpan.');
    }

    public function edit(Request $request, EmployeeBenefit $employeeBenefit)
    {
        $siteId = $employeeBenefit->site_id ?: ($request->query('site') ?: session('site_id'));

        $sites = Site::orderBy('code')->get(['id','code','name']);
        $benefitTypes = ['allowance' => 'Allowance', 'insurance' => 'Insurance', 'leave' => 'Leave', 'bonus' => 'Bonus', 'facility' => 'Facility', 'other' => 'Other'];

        return view('admin.hcm.employee-benefits.edit', compact('employeeBenefit','sites','benefitTypes','siteId'));
    }

    public function update(Request $request, EmployeeBenefit $employeeBenefit)
    {
        $data = $request->validate([
            'site_id'      => 'required|uuid|exists:sites,id',
            'benefit_code' => 'required|string|max:50|unique:employee_benefits,benefit_code,'.$employeeBenefit->id,
            'benefit_name' => 'required|string|max:255',
            'benefit_type' => 'required|string|max:50',
            'amount'       => 'nullable|numeric|min:0',
            'description'  => 'nullable|string',
            'is_active'    => 'boolean',
        ]);

        $employeeBenefit->update($data);

        return redirect()
            ->route('hcm.employee_benefits.index', ['site' => $data['site_id']])
            ->with('success', 'Benefit diperbarui.');
    }

    public function destroy(Request $request, EmployeeBenefit $employeeBenefit)
    {
        $siteId = $employeeBenefit->site_id;
        $employeeBenefit->delete();

        return redirect()
            ->route('hcm.employee_benefits.index', ['site' => $siteId])
            ->with('success', 'Benefit dihapus.');
    }
}
