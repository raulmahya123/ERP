<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\{Timesheet, Attendance, Asset, Shift, User, Site};

class TimesheetController extends Controller
{
    /** List + filter */
    public function index(Request $r)
    {
        // resolve & lock site (request -> session)
        $siteId = $r->input('site_id', session('site_id'));
        if ($siteId) {
            session(['site_id' => $siteId]);
        } else {
            $siteId = session('site_id');
        }

        $perPage = max(1, min(200, (int) $r->input('per_page', 20)));

        $q = Timesheet::query()
            ->with([
                'user:id,name,email,employee_code',
                'shift:id,code,name',
                'equipment:id,code,name',
                'site:id,code,name',
            ])
            ->when($siteId, fn ($q) => $q->where('site_id', $siteId))

            // user_id bisa berisi UUID / nama / employee_code
            ->when($r->filled('user_id'), function ($q) use ($r) {
                $v = (string) $r->input('user_id');
                if (Str::isUuid($v)) {
                    $q->where('user_id', $v);
                } else {
                    $term = Str::lower($v);
                    $q->whereHas('user', function ($uq) use ($term) {
                        $uq->whereRaw('LOWER(name) like ?', ["%{$term}%"])
                           ->orWhereRaw('LOWER(employee_code) like ?', ["%{$term}%"]);
                    });
                }
            })

            // range tanggal
            ->when($r->filled('date_from'), fn ($q, $v) => $q->whereDate('work_date', '>=', $v))
            ->when($r->filled('date_to'),   fn ($q, $v) => $q->whereDate('work_date', '<=', $v))

            // activity_code LIKE (case-insensitive)
            ->when($r->filled('activity_code'), function ($q, $v) {
                $term = Str::lower($v);
                $q->whereRaw('LOWER(activity_code) like ?', ["%{$term}%"]);
            })

            ->orderByDesc('work_date')
            ->orderByDesc('created_at');

        $rows = $q->paginate($perPage)->withQueryString();

        // data pendukung untuk header/filter di Blade
        $sites        = Site::orderBy('name')->get(['id','code','name']);
        $activeSiteId = $siteId;

        // badge jumlah OT pending (site-aware)
        $pendingOT = Timesheet::query()
            ->when($siteId, fn ($qq) => $qq->where('site_id', $siteId))
            ->where('overtime_hours', '>', 0)
            ->where('ot_status', 'pending')
            ->count();

        return view('admin.timesheets.index', compact('rows','sites','activeSiteId','pendingOT'));
    }

    public function create()
    {
        return view('admin.timesheets.create');
    }

    public function store(Request $r)
    {
        $siteId = $r->input('site_id', session('site_id'));

        $data = $r->validate([
            'user_id'        => ['required','uuid','exists:users,id'],
            'shift_id'       => ['nullable','uuid','exists:shifts,id'],
            'equipment_id'   => ['nullable','uuid','exists:assets,id'],
            'work_date'      => ['required','date'],
            'activity_code'  => ['required','string','max:50'],
            'activity_desc'  => ['nullable','string'],
            'hours'          => ['required','numeric','min:0','max:99.99'],
            'overtime_hours' => ['required','numeric','min:0','max:99.99'],
            'cost_center'    => ['nullable','string','max:191'],
            'meta'           => ['nullable','array'],
        ]);

        $ts = new Timesheet($data);
        $ts->site_id = $siteId;

        // set OT status inline
        if (($ts->overtime_hours ?? 0) > 0) {
            $ts->ot_status = 'pending';
            $ts->ot_reason = $r->input('ot_reason', 'Submitted manually');
        } else {
            $ts->ot_status = 'none';
            $ts->ot_reason = null;
        }

        $ts->save();

        return redirect()->route('admin.timesheets.index')->with('success','Timesheet created.');
    }

    public function edit(Timesheet $timesheet)
    {
        return view('admin.timesheets.edit', compact('timesheet'));
    }

    public function update(Request $r, Timesheet $timesheet)
    {
        $data = $r->validate([
            'shift_id'       => ['nullable','uuid','exists:shifts,id'],
            'equipment_id'   => ['nullable','uuid','exists:assets,id'],
            'work_date'      => ['required','date'],
            'activity_code'  => ['required','string','max:50'],
            'activity_desc'  => ['nullable','string'],
            'hours'          => ['required','numeric','min:0','max:99.99'],
            'overtime_hours' => ['required','numeric','min:0','max:99.99'],
            'cost_center'    => ['nullable','string','max:191'],
            'meta'           => ['nullable','array'],
        ]);

        $timesheet->fill($data);

        // jaga status OT
        if (($timesheet->overtime_hours ?? 0) > 0) {
            if ($timesheet->ot_status !== 'approved') {
                $timesheet->ot_status = 'pending'; // reset ke pending kalau belum approved
            }
        } else {
            $timesheet->ot_status      = 'none';
            $timesheet->ot_reason      = null;
            $timesheet->ot_approved_by = null;
            $timesheet->ot_approved_at = null;
        }

        $timesheet->save();

        return redirect()->route('admin.timesheets.index')->with('success','Timesheet updated.');
    }

    public function destroy(Timesheet $timesheet)
    {
        $timesheet->delete();
        return back()->with('success','Timesheet deleted.');
    }

    /* =========================
     * OVERTIME ACTIONS
     * ========================= */

    /** List semua OT (pending/approved/rejected) */
    public function otIndex(Request $r)
    {
        $siteId = $r->input('site_id', session('site_id'));

        $rows = Timesheet::with(['user:id,name,email'])
            ->when($siteId, fn ($q) => $q->where('site_id', $siteId))
            ->where('overtime_hours','>',0)
            ->orderByRaw("FIELD(ot_status,'pending','rejected','approved')")
            ->orderByDesc('work_date')
            ->paginate(max(1, min(200, (int) $r->input('per_page', 20))))
            ->withQueryString();

        return view('admin.overtime.index', compact('rows'));
    }

    /** Karyawan/HR submit OT dari timesheet */
    public function otSubmit(Request $r, Timesheet $timesheet)
    {
        if (($timesheet->overtime_hours ?? 0) <= 0) {
            return back()->with('error','Tidak ada lembur pada timesheet ini.');
        }
        if ($timesheet->ot_status === 'approved') {
            return back()->with('error','Lembur sudah disetujui.');
        }

        $timesheet->ot_status = 'pending';
        $timesheet->ot_reason = $r->input('ot_reason', $timesheet->ot_reason ?: 'Submitted');
        $timesheet->save();

        return back()->with('success','Lembur disubmit (pending).');
    }

    /** Approve OT */
    public function otApprove(Request $r, Timesheet $timesheet)
    {
        if (($timesheet->overtime_hours ?? 0) <= 0) {
            return back()->with('error','Tidak ada lembur pada timesheet ini.');
        }

        $timesheet->ot_status      = 'approved';
        $timesheet->ot_approved_by = $r->user()->id ?? null;
        $timesheet->ot_approved_at = now();
        $timesheet->save();

        return back()->with('success','Lembur disetujui.');
    }

    /** Reject OT */
    public function otReject(Request $r, Timesheet $timesheet)
    {
        if (($timesheet->overtime_hours ?? 0) <= 0) {
            return back()->with('error','Tidak ada lembur pada timesheet ini.');
        }

        $timesheet->ot_status      = 'rejected';
        $timesheet->ot_approved_by = null;
        $timesheet->ot_approved_at = null;
        $timesheet->ot_reason      = $r->input('ot_reason', 'Rejected');
        $timesheet->save();

        return back()->with('success','Lembur ditolak.');
    }
}
