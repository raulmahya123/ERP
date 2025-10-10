<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrewAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CrewAssignmentController extends Controller
{
    public function index(Request $r)
    {
        $q = CrewAssignment::query()
            ->when($r->site_id ?? session('site_id'), fn($qq,$sid)=>$qq->where('site_id',$sid))
            ->when($r->date, fn($qq,$d)=>$qq->whereDate('date',$d))
            ->when($r->shift_slot, fn($qq,$s)=>$qq->where('shift_slot',$s))
            ->when($r->user_id, fn($qq,$u)=>$qq->where('user_id',$u))
            ->orderByDesc('date');

        $assignments = $q->paginate($r->integer('per_page', 25))->appends($r->query());

        if (! $r->wantsJson()) {
            // optional: kirim pilihan shift utk filter
            $shiftSlots = ['A','B','C','D','NON'];
            return view('admin.crew_assignments.index', compact('assignments','shiftSlots'));
        }

        return response()->json($assignments);
    }

    public function create()
    {
        return view('admin.crew_assignments.create');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'site_id'       => ['required','uuid'],
            'date'          => ['required','date'],
            'shift_slot'    => ['required','in:A,B,C,D,NON'],
            'user_id'       => ['required','uuid'],
            'equipment_id'  => ['nullable','uuid'],
            'role'          => ['required','string','max:30'],
            'activity_code' => ['nullable','string','max:50'],
            'remarks'       => ['nullable','string','max:255'],
        ]);

        $data['id'] = (string) Str::uuid();

        CrewAssignment::updateOrCreate(
            [
                'site_id'      => $data['site_id'],
                'date'         => $data['date'],
                'shift_slot'   => $data['shift_slot'],
                'user_id'      => $data['user_id'],
                'equipment_id' => $data['equipment_id'] ?? null,
                'role'         => $data['role'],
            ],
            collect($data)->except(['site_id','date','shift_slot','user_id','equipment_id','role'])->toArray()
        );

        if (! $r->wantsJson()) {
            return redirect()->route('admin.crew-assignments.index')->with('success','Penugasan kru disimpan.');
        }

        return response()->json(['ok'=>true]);
    }

    public function edit(CrewAssignment $crewAssignment)
    {
        return view('admin.crew_assignments.edit', compact('crewAssignment'));
    }

    public function update(Request $r, CrewAssignment $crewAssignment)
    {
        $data = $r->validate([
            'equipment_id'  => ['nullable','uuid'],
            'activity_code' => ['nullable','string','max:50'],
            'remarks'       => ['nullable','string','max:255'],
        ]);

        $crewAssignment->update($data);

        if (! $r->wantsJson()) {
            return back()->with('success','Penugasan kru diperbarui.');
        }

        return response()->json($crewAssignment->refresh());
    }

    public function destroy(Request $r, CrewAssignment $crewAssignment)
    {
        $crewAssignment->delete();

        if (! $r->wantsJson()) {
            return back()->with('success','Penugasan kru dihapus.');
        }

        return response()->json(['ok'=>true]);
    }
}
