<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ManpowerRealization;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ManpowerRealizationController extends Controller
{
    public function index(Request $r)
    {
        $q = ManpowerRealization::query()
            ->when($r->site_id ?? session('site_id'), fn($qq,$sid)=>$qq->where('site_id',$sid))
            ->when($r->date, fn($qq,$d)=>$qq->whereDate('date',$d))
            ->when($r->shift_slot, fn($qq,$s)=>$qq->where('shift_slot',$s))
            ->when($r->department, fn($qq,$d)=>$qq->where('department',$d))
            ->orderByDesc('date');

        $reals = $q->paginate($r->integer('per_page', 25))->appends($r->query());

        if (! $r->wantsJson()) {
            $shiftSlots = ['A','B','C','D','NON'];
            return view('admin.manpower_reals.index', compact('reals','shiftSlots'));
        }

        return response()->json($reals);
    }

    public function create()
    {
        $shiftSlots = ['A','B','C','D','NON'];
        return view('admin.manpower_reals.create', compact('shiftSlots'));
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'site_id'            => ['required','uuid'],
            'date'               => ['required','date'],
            'shift_slot'         => ['required','in:A,B,C,D,NON'],
            'department'         => ['required','string','max:50'],
            'actual_headcount'   => ['required','integer','min:0'],
            'actual_operators'   => ['nullable','integer','min:0'],
            'actual_mechanics'   => ['nullable','integer','min:0'],
            'actual_helpers'     => ['nullable','integer','min:0'],
            'actual_others'      => ['nullable','integer','min:0'],
            'production_tonnage' => ['nullable','numeric','min:0'],
            'manhours'           => ['nullable','numeric','min:0'],
            'meta'               => ['nullable','array'],
        ]);

        $data['id'] = (string) Str::uuid();

        ManpowerRealization::updateOrCreate(
            [
                'site_id'    => $data['site_id'],
                'date'       => $data['date'],
                'shift_slot' => $data['shift_slot'],
                'department' => $data['department'],
            ],
            collect($data)->except(['site_id','date','shift_slot','department'])->toArray()
        );

        if (! $r->wantsJson()) {
            return redirect()->route('admin.manpower-reals.index')->with('success','Realisasi manpower disimpan.');
        }

        return response()->json(['ok'=>true]);
    }

    public function edit(ManpowerRealization $manpowerRealization)
    {
        $shiftSlots = ['A','B','C','D','NON'];
        return view('admin.manpower_reals.edit', ['real'=>$manpowerRealization,'shiftSlots'=>$shiftSlots]);
    }

    public function update(Request $r, ManpowerRealization $manpowerRealization)
    {
        $data = $r->validate([
            'actual_headcount'   => ['sometimes','integer','min:0'],
            'actual_operators'   => ['sometimes','integer','min:0'],
            'actual_mechanics'   => ['sometimes','integer','min:0'],
            'actual_helpers'     => ['sometimes','integer','min:0'],
            'actual_others'      => ['sometimes','integer','min:0'],
            'production_tonnage' => ['sometimes','numeric','min:0'],
            'manhours'           => ['sometimes','numeric','min:0'],
            'meta'               => ['nullable','array'],
        ]);

        $manpowerRealization->update($data);

        if (! $r->wantsJson()) {
            return back()->with('success','Realisasi manpower diperbarui.');
        }

        return response()->json($manpowerRealization->refresh());
    }

    public function destroy(Request $r, ManpowerRealization $manpowerRealization)
    {
        $manpowerRealization->delete();

        if (! $r->wantsJson()) {
            return back()->with('success','Realisasi manpower dihapus.');
        }

        return response()->json(['ok'=>true]);
    }
}
