<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ManpowerPlan;
use App\Models\Site;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ManpowerPlanController extends Controller
{
    /** Slot shift baku */
    private array $slots = ['A','B','C','D','NON'];

    /* ===========================
     * Helpers: Active Site / Resolve Site
     * =========================== */
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

    /** Coba resolve site dari site_id / site_code / site_name; fallback ke active site */
    private function resolveSiteIdFromRequest(Request $r): ?string
    {
        if ($r->filled('site_id')) return (string) $r->input('site_id');

        if ($r->filled('site_code')) {
            $id = Site::where('code', $r->input('site_code'))->value('id');
            if ($id) return (string) $id;
        }

        if ($r->filled('site_name')) {
            $term = Str::lower($r->input('site_name'));
            $id = Site::whereRaw('LOWER(name) like ?', ["%{$term}%"])
                ->orderBy('name')->value('id');
            if ($id) return (string) $id;
        }

        return $this->resolveActiveSiteId($r);
    }

    /** ===========================
     * Index
     * =========================== */
    public function index(Request $r)
    {
        $perPage       = max(1, min(200, (int) $r->input('per_page', 25)));
        $activeSiteId  = $this->resolveActiveSiteId($r);

        $q = ManpowerPlan::query()
            ->when($activeSiteId, fn($qq,$sid)=>$qq->where('site_id',$sid))
            ->when($r->date, fn($qq,$d)=>$qq->whereDate('date',$d))
            ->when($r->shift_slot, fn($qq,$s)=>$qq->where('shift_slot',$s))
            ->when($r->filled('department'), function (Builder $qq) use ($r) {
                $term = Str::lower($r->input('department'));
                $qq->whereRaw('LOWER(department) like ?', ["%{$term}%"]);
            })
            // fulltext ringan
            ->when($r->filled('q'), function (Builder $qq) use ($r) {
                $term = Str::lower($r->q);
                $qq->where(function (Builder $w) use ($term) {
                    $w->whereRaw('LOWER(department) like ?', ["%{$term}%"])
                      ->orWhereRaw('LOWER(note) like ?', ["%{$term}%"]);
                });
            })
            ->orderByDesc('date')->orderBy('department');

        $plans = $q->paginate($perPage)->withQueryString();

        if (! $r->wantsJson()) {
            $shiftSlots = $this->slots;
            $sites      = Site::orderBy('name')->get(['id','code','name']);
            return view('admin.manpower_plans.index', compact('plans','shiftSlots','sites','activeSiteId'));
        }

        return response()->json($plans);
    }

    /** ===========================
     * Create
     * =========================== */
    public function create()
    {
        $shiftSlots = $this->slots;
        return view('admin.manpower_plans.create', compact('shiftSlots'));
    }

    /** ===========================
     * Store
     * =========================== */
    public function store(Request $r)
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
            collect($data)->except(['id','site_id','date','shift_slot','department'])->toArray()
        );

        if (! $r->wantsJson()) {
            return redirect()->route('admin.manpower-plans.index')->with('success','Rencana manpower disimpan.');
        }

        return response()->json(['ok'=>true]);
    }

    /** ===========================
     * Edit
     * =========================== */
    public function edit(ManpowerPlan $manpowerPlan)
    {
        $shiftSlots = $this->slots;
        return view('admin.manpower_plans.edit', ['plan'=>$manpowerPlan,'shiftSlots'=>$shiftSlots]);
    }

    /** ===========================
     * Update
     * =========================== */
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

    /** ===========================
     * Destroy
     * =========================== */
    public function destroy(Request $r, ManpowerPlan $manpowerPlan)
    {
        $manpowerPlan->delete();

        if (! $r->wantsJson()) {
            return back()->with('success','Rencana manpower dihapus.');
        }

        return response()->json(['ok'=>true]);
    }
}
