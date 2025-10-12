<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Timesheet;
use App\Models\Site;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TimesheetController extends Controller
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

    /** Coba resolve site_id dari site_id / site_code / site_name; fallback active site */
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

    /** Coba resolve user_id dari user_id / employee_code / user_name */
    private function resolveUserIdFromRequest(Request $r): ?string
    {
        if ($r->filled('user_id')) return (string) $r->input('user_id');

        if ($r->filled('employee_code')) {
            $id = User::where('employee_code', $r->input('employee_code'))->value('id');
            if ($id) return (string) $id;
        }

        if ($r->filled('user_name')) {
            $term = Str::lower($r->input('user_name'));
            $id = User::whereRaw('LOWER(name) like ?', ["%{$term}%"])
                ->orderBy('name')->value('id');
            if ($id) return (string) $id;
        }

        return null;
    }

    public function index(Request $r)
    {
        $perPage      = max(1, min(200, (int) $r->input('per_page', 25)));
        $activeSiteId = $this->resolveActiveSiteId($r);

        $q = Timesheet::query()
            ->with([
                'user:id,name,employee_code',
                'equipment:id,code,name',
                'shift:id,name',
                'site:id,code,name',
            ])
            // site dikunci (active)
            ->when($activeSiteId, fn ($qb, $sid) => $qb->where('site_id', $sid))

            // USER: dukung UUID atau nama/kode pada param user_id
            ->when($r->filled('user_id'), function (Builder $qb) use ($r) {
                $u = trim((string) $r->input('user_id'));
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

            // filter eksplisit lain
            ->when($r->equipment_id, fn ($qb, $e) => $qb->where('equipment_id', $e))
            ->when($r->activity_code, fn ($qb, $ac) => $qb->where('activity_code', 'like', "%{$ac}%"))
            ->when($r->date, fn ($qb, $d) => $qb->whereDate('work_date', $d))

            // filter user by name / employee_code via param "user" (opsional)
            ->when($r->filled('user'), function (Builder $qb) use ($r) {
                $term = Str::lower($r->input('user'));
                $qb->whereHas('user', function (Builder $uq) use ($term) {
                    $uq->whereRaw('LOWER(name) like ?', ["%{$term}%"])
                       ->orWhereRaw('LOWER(employee_code) like ?', ["%{$term}%"]);
                });
            })

            // filter equipment by code/name (opsional non-UUID)
            ->when($r->filled('equipment'), function (Builder $qb) use ($r) {
                $term = Str::lower($r->input('equipment'));
                $qb->whereHas('equipment', function (Builder $eq) use ($term) {
                    $eq->whereRaw('LOWER(code) like ?', ["%{$term}%"])
                       ->orWhereRaw('LOWER(name) like ?', ["%{$term}%"]);
                });
            })

            // fulltext ringan: q
            ->when($r->filled('q'), function (Builder $qb) use ($r) {
                $term = Str::lower($r->q);
                $qb->where(function (Builder $w) use ($term) {
                    $w->whereHas('user', fn ($uq) =>
                          $uq->whereRaw('LOWER(name) like ?', ["%{$term}%"])
                             ->orWhereRaw('LOWER(employee_code) like ?', ["%{$term}%"]))
                      ->orWhereHas('shift', fn ($sq) =>
                          $sq->whereRaw('LOWER(name) like ?', ["%{$term}%"]))
                      ->orWhereHas('equipment', fn ($eq) =>
                          $eq->whereRaw('LOWER(code) like ?', ["%{$term}%"])
                             ->orWhereRaw('LOWER(name) like ?', ["%{$term}%"]))
                      ->orWhereRaw('LOWER(activity_code) like ?', ["%{$term}%"])
                      ->orWhereRaw('LOWER(activity_desc) like ?', ["%{$term}%"])
                      ->orWhereRaw('LOWER(cost_center) like ?', ["%{$term}%"]);
                });
            })
            ->orderByDesc('work_date');

        if (!$r->wantsJson()) {
            $timesheets = $q->paginate($perPage)->withQueryString();
            // kirim data site utk label di UI
            $sites = Site::orderBy('name')->get(['id','code','name']);
            return view('admin.timesheets.index', compact('timesheets','sites','activeSiteId'));
        }

        return response()->json($q->paginate($perPage));
    }

    public function create()
    {
        // kalau perlu dropdown, bisa load sites/users/equipments di sini
        return view('admin.timesheets.create');
    }

    public function store(Request $r)
    {
        // Auto-resolve site & user kalau tidak kirim UUID
        $resolvedSiteId = $this->resolveSiteIdFromRequest($r);
        if ($resolvedSiteId && !$r->filled('site_id')) {
            $r->merge(['site_id' => $resolvedSiteId]);
        }

        $resolvedUserId = $this->resolveUserIdFromRequest($r);
        if ($resolvedUserId && !$r->filled('user_id')) {
            $r->merge(['user_id' => $resolvedUserId]);
        }

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

        if (!$r->wantsJson()) {
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

        if (!$r->wantsJson()) {
            return back()->with('success','Timesheet diperbarui.');
        }

        return response()->json($timesheet->refresh());
    }

    public function destroy(Request $r, Timesheet $timesheet)
    {
        $timesheet->delete();

        if (!$r->wantsJson()) {
            return back()->with('success','Timesheet dihapus.');
        }

        return response()->json(['ok'=>true]);
    }
}
