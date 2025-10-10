<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmploymentContract;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EmploymentContractController extends Controller
{
    public function index(Request $r)
    {
        $q = EmploymentContract::query()
            ->with(['user','site'])
            ->when($r->site_id ?? session('site_id'), fn($qq,$sid)=>$qq->where('site_id',$sid))
            ->when($r->user_id, fn($qq,$uid)=>$qq->where('user_id',$uid))
            ->when($r->type, fn($qq,$t)=>$qq->where('type',$t))
            ->orderByDesc('start_date');

        $contracts = $q->paginate($r->integer('per_page', 25))->appends($r->query());

        if (! $r->wantsJson()) {
            $types = ['permanent'=>'Permanent','contract'=>'Contract','outsourced'=>'Outsourced'];
            return view('admin.contracts.index', compact('contracts','types'));
        }

        return response()->json($contracts);
    }

    public function create()
    {
        $types = ['permanent'=>'Permanent','contract'=>'Contract','outsourced'=>'Outsourced'];
        return view('admin.contracts.create', compact('types'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'site_id'     => ['nullable','uuid'],
            'user_id'     => ['required','uuid'],
            'type'        => ['required','in:permanent,contract,outsourced'],
            'vendor_name' => ['nullable','string','max:255'],
            'position'    => ['nullable','string','max:100'],
            'base_salary' => ['nullable','numeric','min:0'],
            'start_date'  => ['required','date'],
            'end_date'    => ['nullable','date','after:start_date'],
            'meta'        => ['nullable','array'],
        ]);

        $data['id'] = (string) Str::uuid();

        EmploymentContract::updateOrCreate(
            [
                'user_id'    => $data['user_id'],
                'start_date' => $data['start_date'],
                'site_id'    => $data['site_id'] ?? null,
            ],
            collect($data)->except(['user_id','start_date','site_id'])->toArray()
        );

        if (! $r->wantsJson()) {
            return redirect()->route('admin.contracts.index')->with('success','Kontrak karyawan disimpan.');
        }

        return response()->json(['ok'=>true]);
    }

    public function edit(EmploymentContract $employmentContract)
    {
        $types = ['permanent'=>'Permanent','contract'=>'Contract','outsourced'=>'Outsourced'];
        return view('admin.contracts.edit', ['contract'=>$employmentContract, 'types'=>$types]);
    }

    public function update(Request $r, EmploymentContract $employmentContract)
    {
        $data = $r->validate([
            'type'        => ['sometimes','in:permanent,contract,outsourced'],
            'vendor_name' => ['nullable','string','max:255'],
            'position'    => ['nullable','string','max:100'],
            'base_salary' => ['nullable','numeric','min:0'],
            'end_date'    => ['nullable','date','after:start_date'],
            'meta'        => ['nullable','array'],
        ]);

        $employmentContract->update($data);

        if (! $r->wantsJson()) {
            return back()->with('success','Kontrak karyawan diperbarui.');
        }

        return response()->json($employmentContract->refresh());
    }

    public function destroy(Request $r, EmploymentContract $employmentContract)
    {
        $employmentContract->delete();

        if (! $r->wantsJson()) {
            return back()->with('success','Kontrak karyawan dihapus.');
        }

        return response()->json(['ok'=>true]);
    }
}
