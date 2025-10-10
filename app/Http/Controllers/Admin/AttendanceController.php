<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    public function index(Request $r)
    {
        $q = Attendance::query()
            ->when($r->site_id ?? session('site_id'), fn($q, $sid) => $q->where('site_id', $sid))
            ->when($r->date, fn($q, $d) => $q->whereDate('work_date', $d))
            ->orderByDesc('work_date');

        $attendances = $q->paginate($r->integer('per_page', 25));

        // kalau request dari browser → tampilkan view
        if (! $r->wantsJson()) {
            return view('admin.attendance.index', compact('attendances'));
        }

        // kalau dari API → kirim JSON
        return response()->json($attendances);
    }

    public function create()
    {
        return view('admin.attendance.create');
    }

    public function store(Request $r)
    {
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
            ['site_id' => $data['site_id'], 'user_id' => $data['user_id'], 'work_date' => $data['work_date']],
            collect($data)->except(['id', 'site_id', 'user_id', 'work_date'])->toArray()
        );

        if (! $r->wantsJson()) {
            return redirect()->route('admin.attendance.index')->with('success', 'Data absensi disimpan.');
        }

        return response()->json(['ok' => true]);
    }

    public function edit(Attendance $attendance)
    {
        return view('admin.attendance.edit', compact('attendance'));
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
