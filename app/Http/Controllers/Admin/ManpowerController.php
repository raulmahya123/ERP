<?php
// app/Http/Controllers/Admin/ManpowerController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

use App\Models\ManpowerPlan;
use App\Models\ManpowerRealization;
use App\Models\CrewAssignment;
use App\Models\Site;
use App\Models\User;
use App\Models\Asset as Equipment;

class ManpowerController extends Controller
{
    /** Slot shift baku */
    private array $slots = ['A','B','C','D','NON'];

    /** =========================
     *  Helpers: Site/User/Equip
     *  ========================= */
    private function resolveActiveSiteId(Request $r): ?string
    {
        if ($r->filled('site_id')) {
            $sid = (string) $r->input('site_id');
            session(['site_id' => $sid]);
            return $sid;
        }
        if (session()->has('site_id')) {
            return (string) session('site_id');
        }
        $userSid = optional($r->user())->site_id;
        if ($userSid) {
            session(['site_id' => (string) $userSid]);
            return (string) $userSid;
        }
        $first = Site::orderBy('name')->value('id');
        if ($first) {
            session(['site_id' => (string) $first]);
            return (string) $first;
        }
        return null;
    }

    /** Coba resolve site dari: site_id / site_code / site_name; fallback ke active site */
    private function resolveSiteIdFromRequest(Request $r): ?string
    {
        if ($r->filled('site_id')) return (string) $r->input('site_id');

        if ($r->filled('site_code')) {
            $id = Site::where('code', $r->input('site_code'))->value('id');
            if ($id) return (string) $id;
        }

        if ($r->filled('site_name')) {
            $term = Str::lower($r->input('site_name'));
            $id = Site::whereRaw('LOWER(name) like ?', ["%{$term}%"])->orderBy('name')->value('id');
            if ($id) return (string) $id;
        }

        return $this->resolveActiveSiteId($r);
    }

    /** Coba resolve user dari: user_id / employee_code / user_name */
    private function resolveUserIdFromRequest(Request $r): ?string
    {
        if ($r->filled('user_id')) return (string) $r->input('user_id');

        if ($r->filled('employee_code')) {
            $id = User::where('employee_code', $r->input('employee_code'))->value('id');
            if ($id) return (string) $id;
        }

        if ($r->filled('user_name')) {
            $term = Str::lower($r->input('user_name'));
            $id = User::whereRaw('LOWER(name) like ?', ["%{$term}%"])->orderBy('name')->value('id');
            if ($id) return (string) $id;
        }

        return null;
    }

    /** Coba resolve equipment dari: equipment_id / equipment_code / equipment_name */
    private function resolveEquipmentIdFromRequest(Request $r): ?string
    {
        if ($r->filled('equipment_id')) return (string) $r->input('equipment_id');

        if ($r->filled('equipment_code')) {
            $id = Equipment::where('code', $r->input('equipment_code'))->value('id');
            if ($id) return (string) $id;
        }

        if ($r->filled('equipment_name')) {
            $term = Str::lower($r->input('equipment_name'));
            $id = Equipment::whereRaw('LOWER(name) like ?', ["%{$term}%"])->orderBy('name')->value('id');
            if ($id) return (string) $id;
        }

        return null;
    }

    /** 📊 Dashboard ringkasan manpower */
    public function dashboard(Request $r)
    {
        $activeSiteId = $this->resolveActiveSiteId($r);
        $sid   = $activeSiteId;
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
        $assignments = CrewAssignment::with(['user:id,name,employee_code','equipment:id,code,name'])
            ->where('site_id', $sid)
            ->whereDate('date', $date)
            ->where('shift_slot', $shift)
            ->orderBy('role')
            ->get();

        // Sites untuk label di header (terkunci)
        $sites = Site::orderBy('name')->get(['id','code','name']);

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
            // tambahan untuk UI
            'sites'       => $sites,
            'activeSiteId'=> $activeSiteId,
        ]);
    }

    /** 🗂️ Simpan / update rencana manpower per shift */
    public function storePlan(Request $r)
    {
        // Auto-resolve site kalau tidak kirim UUID
        $resolvedSiteId = $this->resolveSiteIdFromRequest($r);
        if ($resolvedSiteId && !$r->filled('site_id')) {
            $r->merge(['site_id' => $resolvedSiteId]);
        }

        $data = $r->validate([
            'site_id'            => ['required','uuid'],
            'date'               => ['required','date'],
            'shift_slot'         => ['required','in:'.implode(',', $this->slots)],
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
        // Auto-resolve site kalau tidak kirim UUID
        $resolvedSiteId = $this->resolveSiteIdFromRequest($r);
        if ($resolvedSiteId && !$r->filled('site_id')) {
            $r->merge(['site_id' => $resolvedSiteId]);
        }

        $data = $r->validate([
            'site_id'            => ['required','uuid'],
            'date'               => ['required','date'],
            'shift_slot'         => ['required','in:'.implode(',', $this->slots)],
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
        $activeSiteId = $this->resolveActiveSiteId($r);
        $perPage = max(1, min(200, (int) $r->input('per_page', 25)));

        $q = CrewAssignment::query()
            ->where('site_id', $activeSiteId)
            ->when($r->date, fn($qq,$d)=>$qq->whereDate('date',$d))
            ->when($r->filled('shift_slot'), fn($qq,$s)=>$qq->where('shift_slot',$s))
            // filter user by name / employee_code or UUID
            ->when($r->filled('user'), function (Builder $qb) use ($r) {
                $u = trim((string) $r->input('user'));
                $isUuid = preg_match(
                    '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/',
                    $u
                );
                if ($isUuid) {
                    $qb->where('user_id', $u);
                } else {
                    $term = Str::lower($u);
                    $qb->whereHas('user', function (Builder $uq) use ($term) {
                        $uq->whereRaw('LOWER(name) like ?', ["%{$term}%"])
                           ->orWhereRaw('LOWER(employee_code) like ?', ["%{$term}%"]);
                    });
                }
            })
            // filter equipment by code/name or UUID
            ->when($r->filled('equipment'), function (Builder $qb) use ($r) {
                $e = trim((string) $r->input('equipment'));
                $isUuid = preg_match(
                    '/^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$/',
                    $e
                );
                if ($isUuid) {
                    $qb->where('equipment_id', $e);
                } else {
                    $term = Str::lower($e);
                    $qb->whereHas('equipment', function (Builder $eq) use ($term) {
                        $eq->whereRaw('LOWER(code) like ?', ["%{$term}%"])
                           ->orWhereRaw('LOWER(name) like ?', ["%{$term}%"]);
                    });
                }
            })
            ->with(['user:id,name,employee_code','equipment:id,name,code'])
            ->orderByDesc('date');

        $assignments = $q->paginate($perPage)->withQueryString();

        return view('admin.manpower.assignments', [
            'assignments' => $assignments,
            'shiftSlots'  => $this->slots,
            'activeSiteId'=> $activeSiteId,
            'sites'       => Site::orderBy('name')->get(['id','code','name']),
        ]);
    }

    /** ➕ Simpan assignment */
    public function storeAssignment(Request $r)
    {
        // Resolve site/user/equipment jika dikirim non-UUID
        $resolvedSiteId = $this->resolveSiteIdFromRequest($r);
        if ($resolvedSiteId && !$r->filled('site_id')) {
            $r->merge(['site_id' => $resolvedSiteId]);
        }

        $resolvedUserId = $this->resolveUserIdFromRequest($r);
        if ($resolvedUserId && !$r->filled('user_id')) {
            $r->merge(['user_id' => $resolvedUserId]);
        }

        $resolvedEquipId = $this->resolveEquipmentIdFromRequest($r);
        if ($resolvedEquipId && !$r->filled('equipment_id')) {
            $r->merge(['equipment_id' => $resolvedEquipId]);
        }

        $data = $r->validate([
            'site_id'      => ['required','uuid'],
            'date'         => ['required','date'],
            'shift_slot'   => ['required','in:'.implode(',', $this->slots)],
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
