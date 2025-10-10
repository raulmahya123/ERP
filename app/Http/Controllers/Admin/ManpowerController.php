<?php
// app/Http/Controllers/Admin/ManpowerController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\ManpowerPlan;
use App\Models\ManpowerRealization;
use App\Models\CrewAssignment;

class ManpowerController extends Controller
{
    /** Slot shift baku */
    private array $slots = ['A','B','C','D','NON'];

    /** 📊 Dashboard ringkasan manpower */
    public function dashboard(Request $r)
    {
        $sid   = $r->input('site_id', session('site_id'));
        $date  = $r->input('date', now()->toDateString());
        $shift = $r->input('shift_slot', 'A');
        if (!in_array($shift, $this->slots, true)) $shift = 'A';

        // ========== Agregasi plan & real untuk ringkasan ==========
        $plansAgg = ManpowerPlan::select('shift_slot','department', DB::raw('SUM(planned_headcount) as total'))
            ->where('site_id', $sid)
            ->whereDate('date', $date)
            ->groupBy('shift_slot','department')
            ->get();

        $realsAgg = ManpowerRealization::select('shift_slot','department', DB::raw('SUM(actual_headcount) as total'))
            ->where('site_id', $sid)
            ->whereDate('date', $date)
            ->groupBy('shift_slot','department')
            ->get();

        // dept unik + terurut
        $depts = $plansAgg->pluck('department')
            ->merge($realsAgg->pluck('department'))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        // index untuk O(1) lookup
        $planIndex = $plansAgg->mapWithKeys(fn($row) => [
            $row->shift_slot.'|'.$row->department => (int) $row->total
        ]);
        $realIndex = $realsAgg->mapWithKeys(fn($row) => [
            $row->shift_slot.'|'.$row->department => (int) $row->total
        ]);

        $planMatrix = [];
        $realMatrix = [];
        foreach ($this->slots as $s) {
            foreach ($depts as $d) {
                $key = $s.'|'.$d;
                $planMatrix[$s][$d] = $planIndex[$key] ?? 0;
                $realMatrix[$s][$d] = $realIndex[$key] ?? 0;
            }
        }

        // ========== Detail (shift terpilih) ==========
        $plans = ManpowerPlan::select('department','planned_headcount','note')
            ->where('site_id', $sid)
            ->whereDate('date', $date)
            ->where('shift_slot', $shift)
            ->orderBy('department')
            ->get();

        $real = ManpowerRealization::select('department','actual_headcount','manhours','production_tonnage')
            ->where('site_id', $sid)
            ->whereDate('date', $date)
            ->where('shift_slot', $shift)
            ->orderBy('department')
            ->get();

        $headcount_plan   = (int) $plans->sum('planned_headcount');
        $headcount_actual = (int) $real->sum('actual_headcount');
        $sum_mh           = (float) $real->sum('manhours');
        $sum_prod         = (float) $real->sum('production_tonnage');

        $kpi = [
            'headcount_plan'      => $headcount_plan,
            'headcount_actual'    => $headcount_actual,
            'crew_fill_rate'      => $headcount_plan > 0 ? ($headcount_actual / $headcount_plan) * 100 : 0,
            'productivity_per_mh' => $sum_mh > 0 ? ($sum_prod / $sum_mh) : 0,
        ];

        // ========== Mapping crew (shift terpilih) ==========
        $assignments = CrewAssignment::with(['user:id,name','equipment:id,code,name'])
            ->where('site_id', $sid)
            ->whereDate('date', $date)
            ->where('shift_slot', $shift)
            ->orderBy('role')
            ->get();

        return view('admin.manpower.dashboard', [
            'date'        => $date,
            'shift'       => $shift,
            'kpi'         => $kpi,
            'plans'       => $plans,
            'real'        => $real,
            'assignments' => $assignments,
            'shifts'      => $this->slots,
            'depts'       => $depts,
            'planMatrix'  => $planMatrix,
            'realMatrix'  => $realMatrix,
        ]);
    }

    /** 🗂️ Simpan / update rencana manpower per shift */
    public function storePlan(Request $r)
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
        ]);

        ManpowerPlan::updateOrCreate(
            [
                'site_id'    => $data['site_id'],
                'date'       => $data['date'],
                'shift_slot' => $data['shift_slot'],
                'department' => $data['department'],
            ],
            collect($data)->except(['site_id','date','shift_slot','department'])->toArray()
        );

        return back()->with('success','Rencana manpower tersimpan.');
    }

    /** 🧍‍♂️ Simpan realisasi manpower per shift */
    public function storeRealization(Request $r)
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
        ]);

        ManpowerRealization::updateOrCreate(
            [
                'site_id'    => $data['site_id'],
                'date'       => $data['date'],
                'shift_slot' => $data['shift_slot'],
                'department' => $data['department'],
            ],
            collect($data)->except(['site_id','date','shift_slot','department'])->toArray()
        );

        return back()->with('success','Realisasi manpower tersimpan.');
    }

    /** 👷 Index assignment */
    public function assignments(Request $r)
    {
        $sid = $r->input('site_id', session('site_id'));

        $q = CrewAssignment::query()
            ->where('site_id', $sid)
            ->when($r->date, fn($qq,$d)=>$qq->whereDate('date',$d))
            ->with(['user:id,name','equipment:id,name,code'])
            ->orderByDesc('date');

        $assignments = $q->paginate($r->integer('per_page',25))->appends($r->query());

        return view('admin.manpower.assignments', [
            'assignments' => $assignments,
            'shiftSlots'  => $this->slots,
        ]);
    }

    /** ➕ Simpan assignment */
    public function storeAssignment(Request $r)
    {
        $data = $r->validate([
            'site_id'      => ['required','uuid'],
            'date'         => ['required','date'],
            'shift_slot'   => ['required','in:A,B,C,D,NON'],
            'user_id'      => ['required','uuid'],
            'equipment_id' => ['nullable','uuid'],
            'role'         => ['required','string','max:30'],
            'activity_code'=> ['nullable','string','max:50'],
            'remarks'      => ['nullable','string','max:255'],
        ]);

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

        return back()->with('success','Crew assignment tersimpan.');
    }
}
