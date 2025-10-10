<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ManpowerPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ManpowerPlanController extends Controller
{
    public function index(Request $r)
    {
        $q = ManpowerPlan::query()
            ->when($r->site_id ?? session('site_id'), fn($qq,$sid)=>$qq->where('site_id',$sid))
            ->when($r->date, fn($qq,$d)=>$qq->whereDate('date',$d))
            ->when($r->shift_slot, fn($qq,$s)=>$qq->where('shift_slot',$s))
            ->when($r->department, fn($qq,$d)=>$qq->where('department',$d))
            ->orderByDesc('date');

        $plans = $q->paginate($r->integer('per_page', 25))->appends($r->query());

        if (! $r->wantsJson()) {
            $shiftSlots = ['A','B','C','D','NON'];
            return view('admin.manpower_plans.index', compact('plans','shiftSlots'));
        }

        return response()->json($plans);
    }

    public function create()
    {
        $shiftSlots = ['A','B','C','D','NON'];
        return view('admin.manpower_plans.create', compact('shiftSlots'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'site_id'            => ['required','uuid'],
            'date'               => ['required','date'],
            'shift_slot'         => ['required','in:A,B,C,D,NON'],
            'department'         => ['required','string','max:50'],
            'planned_headcount'  => ['required','integer','min:0'],
            'planned_operators'  => ['nullable','integer','min:0'],
            'planned_mechanics'  => ['nullable','integer','min:0'],
            'planned_helpers'    => ['nullable','integer','min:0'],
            'planned_others'     => ['nullable','integer','min:0'],
            'note'               => ['nullable','string','max:200'],
            'meta'               => ['nullable','array'],
        ]);

        $data['id'] = (string) Str::uuid();

        ManpowerPlan::updateOrCreate(
            [
                'site_id'    => $data['site_id'],
                'date'       => $data['date'],
                'shift_slot' => $data['shift_slot'],
                'department' => $data['department'],
            ],
            collect($data)->except(['site_id','date','shift_slot','department'])->toArray()
        );

        if (! $r->wantsJson()) {
            return redirect()->route('admin.manpower-plans.index')->with('success','Rencana manpower disimpan.');
        }

        return response()->json(['ok'=>true]);
    }

    public function edit(ManpowerPlan $manpowerPlan)
    {
        $shiftSlots = ['A','B','C','D','NON'];
        return view('admin.manpower_plans.edit', ['plan'=>$manpowerPlan,'shiftSlots'=>$shiftSlots]);
    }

    public function update(Request $r, ManpowerPlan $manpowerPlan)
    {
        $data = $r->validate([
            'planned_headcount'  => ['sometimes','integer','min:0'],
            'planned_operators'  => ['sometimes','integer','min:0'],
            'planned_mechanics'  => ['sometimes','integer','min:0'],
            'planned_helpers'    => ['sometimes','integer','min:0'],
            'planned_others'     => ['sometimes','integer','min:0'],
            'note'               => ['nullable','string','max:200'],
            'meta'               => ['nullable','array'],
        ]);

        $manpowerPlan->update($data);

        if (! $r->wantsJson()) {
            return back()->with('success','Rencana manpower diperbarui.');
        }

        return response()->json($manpowerPlan->refresh());
    }

    public function destroy(Request $r, ManpowerPlan $manpowerPlan)
    {
        $manpowerPlan->delete();

        if (! $r->wantsJson()) {
            return back()->with('success','Rencana manpower dihapus.');
        }

        return response()->json(['ok'=>true]);
    }
}
