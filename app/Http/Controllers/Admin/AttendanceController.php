<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{Attendance, Location, Timesheet, Shift};

class AttendanceController extends Controller
{
    public function index(Request $r)
    {
        $siteId = $r->input('site_id', session('site_id'));

        $q = Attendance::with([
                'user:id,name,email,employee_code',
                'shift:id,code,name',
                'locationIn:id,name',
                'locationOut:id,name'
            ])
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))

            // kompatibel dgn filter lama
            ->when($r->user_id, fn($q, $v) => $q->where('user_id', $v))
            ->when($r->date_from, fn($q, $v) => $q->whereDate('work_date', '>=', $v))
            ->when($r->date_to, fn($q, $v)   => $q->whereDate('work_date', '<=', $v))

            // kompatibel dgn filter baru di view (date tunggal + user string)
            ->when($r->date, fn($q, $d) => $q->whereDate('work_date', $d))
            ->when($r->user, function ($q, $v) {
                $like = '%'.$v.'%';
                $q->where(function ($w) use ($like) {
                    $w->whereHas('user', function ($u) use ($like) {
                        $u->where('name', 'like', $like)
                          ->orWhere('employee_code', 'like', $like)
                          ->orWhere('email', 'like', $like);
                    })
                    // tetap izinkan cari langsung pakai UUID di kolom user_id
                    ->orWhere('user_id', 'like', $like);
                });
            })
            ->orderByDesc('work_date')
            ->orderByDesc('check_in_at');

        $rows = $q->paginate(20)->withQueryString();

        // === tambahan: kirim shifts utk dropdown "Input Absensi" ===
        $shifts = Shift::query()
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderBy('code')
            ->get(['id','code','start_at','end_at']);

        // (opsional) kirim sites utk label site di header; aman kalau tabel tidak ada
        $sites = collect();
        try {
            if (DB::getSchemaBuilder()->hasTable('sites')) {
                $sites = DB::table('sites')->select('id','code','name')->get();
            }
        } catch (\Throwable $e) {
            // abaikan
        }

        return view('admin.attendance.index', [
            'rows'         => $rows,
            'shifts'       => $shifts,
            'sites'        => $sites,
            'activeSiteId' => $siteId,
        ]);
    }

    public function create()
    {
        // optional: sediakan master utk form create
        $siteId = session('site_id');
        $shifts = Shift::query()
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderBy('code')
            ->get(['id','code','start_at','end_at']);
        $locations = Location::query()->orderBy('name')->get(['id','name']);

        return view('admin.attendance.create', compact('shifts','locations'));
    }

    public function store(Request $r)
    {
        $siteId = $r->input('site_id', session('site_id'));

        $data = $r->validate([
            'user_id'             => ['required','uuid','exists:users,id'],
            'work_date'           => ['required','date'],
            'shift_id'            => ['nullable','uuid','exists:shifts,id'],
            'source'              => ['required','in:manual,fingerprint,mobile_gps'],
            'check_in_at'         => ['nullable','date'],
            'check_out_at'        => ['nullable','date','after_or_equal:check_in_at'],
            'location_in_id'      => ['nullable','uuid','exists:locations,id'],
            'location_out_id'     => ['nullable','uuid','exists:locations,id'],
            'gps_in_lat'          => ['nullable','numeric','between:-90,90'],
            'gps_in_lng'          => ['nullable','numeric','between:-180,180'],
            'gps_out_lat'         => ['nullable','numeric','between:-90,90'],
            'gps_out_lng'         => ['nullable','numeric','between:-180,180'],
            'device_id'           => ['nullable','string','max:191'],
            'late_minutes'        => ['nullable','integer','min:0'],
            'early_leave_minutes' => ['nullable','integer','min:0'],
            'status'              => ['required','in:present,absent,leave,permit,sick,off,unknown'],
            'flags'               => ['nullable','array'],
        ]);

        $att = new Attendance($data);
        $att->site_id = $siteId;

        // hitung work_minutes kalau ada in/out (ambil break_minutes shift secara aman)
        if (!empty($att->check_in_at) && !empty($att->check_out_at)) {
            $total = $att->check_out_at->diffInMinutes($att->check_in_at);
            $break = 0;
            if (!empty($att->shift_id)) {
                $break = (int) (DB::table('shifts')->where('id', $att->shift_id)->value('break_minutes') ?? 0);
            }
            $att->work_minutes = max(0, $total - $break);
        }

        $att->save();

        // sinkron timesheet 'attendance' opsional (kalau admin isi in/out lengkap)
        if ($att->check_in_at && $att->check_out_at) {
            $hours = round(($att->work_minutes ?? 0) / 60, 2);
            $ot    = max(0, round($hours - 8.00, 2));
            Timesheet::updateOrCreate(
                [
                    'site_id'       => $siteId,
                    'user_id'       => $att->user_id,
                    'work_date'     => $att->work_date,
                    'activity_code' => 'attendance',
                    'equipment_id'  => null,
                ],
                [
                    'shift_id'       => $att->shift_id,
                    'activity_desc'  => 'Admin set',
                    'hours'          => $hours,
                    'overtime_hours' => $ot,
                    'attendance_id'  => $att->id,
                    'ot_status'      => $ot > 0 ? 'pending' : 'none',
                ]
            );
        }

        return redirect()->route('admin.attendance.index')->with('success','Attendance created.');
    }

    public function edit(Attendance $attendance)
    {
        // optional: sediakan master utk form edit
        $siteId   = $attendance->site_id ?? session('site_id');
        $shifts   = Shift::query()
                        ->when($siteId, fn($q) => $q->where('site_id', $siteId))
                        ->orderBy('code')->get(['id','code','start_at','end_at']);
        $locations= Location::query()->orderBy('name')->get(['id','name']);

        return view('admin.attendance.edit', compact('attendance','shifts','locations'));
    }

    public function update(Request $r, Attendance $attendance)
    {
        $data = $r->validate([
            'shift_id'            => ['nullable','uuid','exists:shifts,id'],
            'source'              => ['required','in:manual,fingerprint,mobile_gps'],
            'check_in_at'         => ['nullable','date'],
            'check_out_at'        => ['nullable','date','after_or_equal:check_in_at'],
            'location_in_id'      => ['nullable','uuid','exists:locations,id'],
            'location_out_id'     => ['nullable','uuid','exists:locations,id'],
            'gps_in_lat'          => ['nullable','numeric','between:-90,90'],
            'gps_in_lng'          => ['nullable','numeric','between:-180,180'],
            'gps_out_lat'         => ['nullable','numeric','between:-90,90'],
            'gps_out_lng'         => ['nullable','numeric','between:-180,180'],
            'device_id'           => ['nullable','string','max:191'],
            'late_minutes'        => ['nullable','integer','min:0'],
            'early_leave_minutes' => ['nullable','integer','min:0'],
            'status'              => ['required','in:present,absent,leave,permit,sick,off,unknown'],
            'flags'               => ['nullable','array'],
        ]);

        $attendance->fill($data);

        // Recalc work_minutes jika ada in/out (pakai break_minutes shift)
        if ($attendance->check_in_at && $attendance->check_out_at) {
            $total = $attendance->check_out_at->diffInMinutes($attendance->check_in_at);
            $break = 0;
            if (!empty($attendance->shift_id)) {
                $break = (int) (DB::table('shifts')->where('id', $attendance->shift_id)->value('break_minutes') ?? 0);
            }
            $attendance->work_minutes = max(0, $total - $break);
        }

        $attendance->save();

        // Sync timesheet 'attendance'
        if ($attendance->check_in_at && $attendance->check_out_at) {
            $hours = round(($attendance->work_minutes ?? 0) / 60, 2);
            $ot    = max(0, round($hours - 8.00, 2));

            $ts = Timesheet::updateOrCreate(
                [
                    'site_id'       => $attendance->site_id,
                    'user_id'       => $attendance->user_id,
                    'work_date'     => $attendance->work_date,
                    'activity_code' => 'attendance',
                    'equipment_id'  => null,
                ],
                [
                    'shift_id'       => $attendance->shift_id,
                    'activity_desc'  => 'Admin update',
                    'hours'          => $hours,
                    'overtime_hours' => $ot,
                    'attendance_id'  => $attendance->id,
                ]
            );

            if ($ot > 0) {
                if ($ts->ot_status !== 'approved') {
                    $ts->ot_status = 'pending';
                }
            } else {
                $ts->ot_status      = 'none';
                $ts->ot_reason      = null;
                $ts->ot_approved_by = null;
                $ts->ot_approved_at = null;
            }
            $ts->save();
        }

        return redirect()->route('admin.attendance.index')->with('success','Attendance updated.');
    }

    public function destroy(Attendance $attendance)
    {
        $attendance->delete();
        return back()->with('success','Attendance deleted.');
    }
}
