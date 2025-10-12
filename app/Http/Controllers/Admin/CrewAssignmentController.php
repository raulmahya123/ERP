<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CrewAssignment;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CrewAssignmentController extends Controller
{
    /** Resolusi site aktif: request → session → user → first site */
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

    public function index(Request $r)
    {
        $perPage      = max(1, min(200, (int) $r->input('per_page', 25)));
        $activeSiteId = $this->resolveActiveSiteId($r);

        $q = CrewAssignment::query()
            ->with([
                'user:id,name,employee_code',
                'equipment:id,code,name',
            ])
            // site dikunci
            ->when($activeSiteId, fn (Builder $qb, $sid) => $qb->where('site_id', $sid))

            // filter eksplisit (UUID tetap didukung)
            ->when($r->filled('date'), fn (Builder $qb, $d) => $qb->whereDate('date', $d))
            ->when($r->filled('shift_slot'), fn (Builder $qb, $s) => $qb->where('shift_slot', $s))
            ->when($r->filled('user_id'), fn (Builder $qb, $u) => $qb->where('user_id', $u))
            ->when($r->filled('equipment_id'), fn (Builder $qb, $e) => $qb->where('equipment_id', $e))

            // filter user by name / employee_code (tanpa RAW)
            ->when($r->filled('user'), function (Builder $qb) use ($r) {
                $term = $r->input('user');
                $qb->whereHas('user', function (Builder $uq) use ($term) {
                    $uq->where('name', 'like', "%{$term}%")
                       ->orWhere('employee_code', 'like', "%{$term}%");
                });
            })

            // filter equipment by code/name (opsional non-UUID)
            ->when($r->filled('equipment'), function (Builder $qb) use ($r) {
                $term = $r->input('equipment');
                $qb->whereHas('equipment', function (Builder $eq) use ($term) {
                    $eq->where('code', 'like', "%{$term}%")
                       ->orWhere('name', 'like', "%{$term}%");
                });
            })

            // pencarian ringan
            ->when($r->filled('q'), function (Builder $qb) use ($r) {
                $t = $r->input('q');
                $qb->where(function (Builder $w) use ($t) {
                    $w->where('role', 'like', "%{$t}%")
                      ->orWhere('activity_code', 'like', "%{$t}%")
                      ->orWhere('remarks', 'like', "%{$t}%")
                      ->orWhereHas('user', fn (Builder $uq) =>
                          $uq->where('name', 'like', "%{$t}%")
                             ->orWhere('employee_code', 'like', "%{$t}%"))
                      ->orWhereHas('equipment', fn (Builder $eq) =>
                          $eq->where('code', 'like', "%{$t}%")
                             ->orWhere('name', 'like', "%{$t}%"));
                });
            })
            ->orderByDesc('date')
            ->orderBy('shift_slot')
            ->orderBy('role');

        $assignments = $q->paginate($perPage)->withQueryString();

        if (!$r->wantsJson()) {
            $shiftSlots = ['A','B','C','D','NON'];
            $sites      = Site::orderBy('name')->get(['id','code','name']);
            return view('admin.crew_assignments.index', compact('assignments','shiftSlots','sites','activeSiteId'));
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

        if (!$r->wantsJson()) {
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

        if (!$r->wantsJson()) {
            return back()->with('success','Penugasan kru diperbarui.');
        }

        return response()->json($crewAssignment->refresh());
    }

    public function destroy(Request $r, CrewAssignment $crewAssignment)
    {
        $crewAssignment->delete();

        if (!$r->wantsJson()) {
            return back()->with('success','Penugasan kru dihapus.');
        }

        return response()->json(['ok'=>true]);
    }
}
