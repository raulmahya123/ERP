<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Site;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    /** Resolve & persist default active site_id (request → session → user → first site) */
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
        $perPage       = max(1, min(200, (int) $r->input('per_page', 25)));
        $activeSiteId  = $this->resolveActiveSiteId($r);

        $q = Attendance::query()
            ->with([
                'user:id,name,employee_code',
                'shift:id,name',
                'site:id,code,name',
            ])
            ->when($activeSiteId, fn ($q, $sid) => $q->where('site_id', $sid))
            ->when($r->date, fn ($q, $d) => $q->whereDate('work_date', $d))
            ->when($r->filled('q'), function (Builder $qb) use ($r) {
                $term = Str::lower($r->q);
                $qb->where(function (Builder $w) use ($term) {
                    $w->whereHas('user',  fn ($uq) => $uq->whereRaw('LOWER(name) like ?', ["%{$term}%"]))
                      ->orWhereHas('shift', fn ($sq) => $sq->whereRaw('LOWER(name) like ?', ["%{$term}%"]))
                      ->orWhereRaw('LOWER(source) like ?', ["%{$term}%"])
                      ->orWhereRaw('LOWER(status) like ?', ["%{$term}%"]);
                });
            })
            ->when($r->filled('source'), fn ($q, $src) => $q->where('source', $src))
            ->when($r->filled('status'), fn ($q, $st) => $q->where('status', $st))
            ->when($r->filled('check_in_from'), fn ($q, $t) => $q->whereTime('check_in_at', '>=', $t))
            ->when($r->filled('check_out_to'), fn ($q, $t) => $q->whereTime('check_out_at', '<=', $t))
            ->orderByDesc('work_date')->orderByDesc('check_in_at');

        $attendances = $q->paginate($perPage)->withQueryString();

        if (! $r->wantsJson()) {
            $sites = Site::orderBy('name')->get(['id','code','name']);
            return view('admin.attendance.index', compact('attendances','sites','activeSiteId'));
        }

        return response()->json($attendances);
    }

    public function create()
    {
        $sites = Site::orderBy('name')->get(['id','code','name']);
        return view('admin.attendance.create', compact('sites'));
    }

    public function store(Request $r)
    {
        // defaultkan site_id ke active site kalau tidak dikirim form/API
        $activeSiteId = $this->resolveActiveSiteId($r);
        if (! $r->filled('site_id') && $activeSiteId) {
            $r->merge(['site_id' => $activeSiteId]);
        }

        $data = $r->validate([
            'site_id'       => ['required', 'uuid'],
            'user_id'       => ['required', 'uuid'],
            'work_date'     => ['required', 'date'],
            'shift_id'      => ['nullable', 'uuid'],
            'source'        => ['required', 'in:manual,fingerprint,mobile_gps'],
            'check_in_at'   => ['nullable', 'date'],
            'check_out_at'  => ['nullable', 'date'],
            'status'        => ['nullable', 'string'],
        ]);

        $data['id'] = (string) Str::uuid();

        Attendance::updateOrCreate(
            [
                'site_id'   => $data['site_id'],
                'user_id'   => $data['user_id'],
                'work_date' => $data['work_date'],
            ],
            collect($data)->except(['id','site_id','user_id','work_date'])->toArray()
        );

        if (! $r->wantsJson()) {
            return redirect()->route('admin.attendance.index')->with('success', 'Data absensi disimpan.');
        }

        return response()->json(['ok' => true]);
    }

    public function edit(Attendance $attendance)
    {
        $sites = Site::orderBy('name')->get(['id','code','name']);
        return view('admin.attendance.edit', compact('attendance','sites'));
    }

    public function update(Request $r, Attendance $attendance)
    {
        $attendance->update($r->only([
            'check_in_at', 'check_out_at', 'status', 'source', 'flags',
            'late_minutes', 'early_leave_minutes', 'overtime_minutes', 'work_minutes'
        ]));

        if (! $r->wantsJson()) {
            return redirect()->back()->with('success', 'Data absensi diperbarui.');
        }

        return response()->json($attendance->refresh());
    }

    public function destroy(Request $r, Attendance $attendance)
    {
        $attendance->delete();

        if (! $r->wantsJson()) {
            return redirect()->back()->with('success', 'Data absensi dihapus.');
        }

        return response()->json(['ok' => true]);
    }
}
