<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Timesheet;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TimesheetController extends Controller
{
    public function index(Request $r)
    {
        $q = Timesheet::query()
            ->with(['user:id,name','equipment:id,code,name','shift:id,name'])
            ->when($r->site_id ?? session('site_id'), fn($q,$sid)=>$q->where('site_id',$sid))
            ->when($r->user_id, fn($q,$u)=>$q->where('user_id',$u))
            ->when($r->equipment_id, fn($q,$e)=>$q->where('equipment_id',$e))
            ->when($r->activity_code, fn($q,$ac)=>$q->where('activity_code','like',"%$ac%"))
            ->when($r->date, fn($q,$d)=>$q->whereDate('work_date',$d))
            ->orderByDesc('work_date');

        if (! $r->wantsJson()) {
            $timesheets = $q->paginate($r->integer('per_page',25))->appends($r->query());
            return view('admin.timesheets.index', compact('timesheets'));
        }

        return response()->json($q->paginate($r->integer('per_page',25)));
    }

    public function create()
    {
        return view('admin.timesheets.create');
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'site_id'        => ['required','uuid'],
            'user_id'        => ['required','uuid'],
            'shift_id'       => ['nullable','uuid'],
            'equipment_id'   => ['nullable','uuid'],
            'work_date'      => ['required','date'],
            'activity_code'  => ['required','string','max:50'],
            'activity_desc'  => ['nullable','string'],
            'hours'          => ['nullable','numeric','min:0'],
            'overtime_hours' => ['nullable','numeric','min:0'],
            'cost_center'    => ['nullable','string','max:50'],
            'meta'           => ['nullable','array'],
        ]);

        $data['id'] = (string) Str::uuid();

        Timesheet::updateOrCreate(
            [
                'site_id'       => $data['site_id'],
                'user_id'       => $data['user_id'],
                'work_date'     => $data['work_date'],
                'activity_code' => $data['activity_code'],
                'equipment_id'  => $data['equipment_id'] ?? null,
            ],
            collect($data)->except(['id','site_id','user_id','work_date','activity_code','equipment_id'])->toArray()
        );

        if (! $r->wantsJson()) {
            return redirect()->route('admin.timesheets.index')->with('success','Timesheet disimpan.');
        }

        return response()->json(['ok'=>true]);
    }

    public function edit(Timesheet $timesheet)
    {
        return view('admin.timesheets.edit', compact('timesheet'));
    }

    public function update(Request $r, Timesheet $timesheet)
    {
        $data = $r->validate([
            'activity_desc'  => ['nullable','string'],
            'hours'          => ['nullable','numeric','min:0'],
            'overtime_hours' => ['nullable','numeric','min:0'],
            'cost_center'    => ['nullable','string','max:50'],
            'meta'           => ['nullable','array'],
        ]);

        $timesheet->update($data);

        if (! $r->wantsJson()) {
            return back()->with('success','Timesheet diperbarui.');
        }

        return response()->json($timesheet->refresh());
    }

    public function destroy(Request $r, Timesheet $timesheet)
    {
        $timesheet->delete();

        if (! $r->wantsJson()) {
            return back()->with('success','Timesheet dihapus.');
        }

        return response()->json(['ok'=>true]);
    }
}
