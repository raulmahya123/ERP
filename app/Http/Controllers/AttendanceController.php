<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\{Attendance, Location, Timesheet};
use App\Services\GeoService;

class AttendanceController extends Controller
{
    /** Halaman tap: pilih lokasi, lihat jarak, tombol Check-In / Check-Out */
    public function tapPage(Request $r)
    {
        $user   = $r->user();
        $siteId = $r->input('site_id', session('site_id'));

        $locations = Location::query()
            ->when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderBy('name')
            ->get(['id','name','latitude','longitude','geofence_radius_m']);

        $today = now()->toDateString();
        $todayAtt = Attendance::where('site_id', $siteId)
            ->where('user_id', $user->id)
            ->whereDate('work_date', $today)
            ->first();

        return view('attendance.tap', compact('locations', 'todayAtt', 'siteId'));
    }

    /** Check-In (boleh di luar jangkauan, cuma ditandai) */
    public function checkIn(Request $r)
    {
        $user   = $r->user();
        $siteId = $r->input('site_id', session('site_id'));
        $today  = now()->toDateString();

        $data = $r->validate([
            'location_id' => ['required','uuid','exists:locations,id'],
            'lat'         => ['required','numeric','between:-90,90'],
            'lng'         => ['required','numeric','between:-180,180'],
            'device_id'   => ['nullable','string','max:191'],
        ]);

        $loc    = Location::findOrFail($data['location_id']);
        $radius = (int)($loc->geofence_radius_m ?: 100);
        $dist   = GeoService::distance($data['lat'], $data['lng'], (float)$loc->latitude, (float)$loc->longitude);

        $att = Attendance::firstOrCreate(
            ['site_id'=>$siteId, 'user_id'=>$user->id, 'work_date'=>$today],
            ['source'=>'mobile_gps','status'=>'present']
        );

        abort_if($att->check_in_at, 409, 'Sudah Check-In.');

        // (opsional) set shift dari roster kalau mau — bisa tambahkan di sini

        $att->fill([
            'source'                => 'mobile_gps',
            'check_in_at'           => now(),
            'gps_in_lat'            => $data['lat'],
            'gps_in_lng'            => $data['lng'],
            'location_in_id'        => $loc->id,
            'outside_geofence_in'   => $dist > $radius,
            'distance_in_m'         => $dist,
            'device_id'             => $data['device_id'] ?? $att->device_id,
            'status'                => 'present',
        ])->save();

        return back()->with('ok', 'Check-In berhasil'.($dist>$radius ? ' (Di luar jangkauan)' : ''));
    }

    /** Check-Out: hitung menit kerja, upsert timesheet, set OT pending jika > 8 jam */
    public function checkOut(Request $r)
    {
        $user   = $r->user();
        $siteId = $r->input('site_id', session('site_id'));
        $today  = now()->toDateString();

        $data = $r->validate([
            'location_id' => ['required','uuid','exists:locations,id'],
            'lat'         => ['required','numeric','between:-90,90'],
            'lng'         => ['required','numeric','between:-180,180'],
            'device_id'   => ['nullable','string','max:191'],
        ]);

        $att = Attendance::where('site_id',$siteId)
            ->where('user_id',$user->id)
            ->whereDate('work_date',$today)
            ->first();

        abort_if(!$att || !$att->check_in_at, 409, 'Belum Check-In.');
        abort_if($att->check_out_at, 409, 'Sudah Check-Out.');

        $loc    = Location::findOrFail($data['location_id']);
        $radius = (int)($loc->geofence_radius_m ?: 100);
        $dist   = GeoService::distance($data['lat'], $data['lng'], (float)$loc->latitude, (float)$loc->longitude);

        DB::transaction(function () use ($att,$loc,$dist,$radius,$data,$siteId,$user) {
            // 1) Simpan checkout
            $att->fill([
                'check_out_at'          => now(),
                'gps_out_lat'           => $data['lat'],
                'gps_out_lng'           => $data['lng'],
                'location_out_id'       => $loc->id,
                'outside_geofence_out'  => $dist > $radius,
                'distance_out_m'        => $dist,
                'device_id'             => $data['device_id'] ?? $att->device_id,
                'status'                => 'present',
            ])->save();

            // 2) Hitung menit kerja (dikurangi break shift jika ada)
            $start = $att->check_in_at;
            $end   = $att->check_out_at;
            $total = $end->diffInMinutes($start);
            $break = optional($att->shift)->break_minutes ?? 0;
            $work  = max(0, $total - $break);
            $att->work_minutes = $work;
            $att->save();

            // 3) Upsert timesheet (activity_code = 'attendance', equipment null)
            $hours = round($work / 60, 2);
            $ot    = max(0, round($hours - 8.00, 2));

            $ts = Timesheet::updateOrCreate(
                [
                    'site_id'       => $siteId,
                    'user_id'       => $user->id,
                    'work_date'     => $att->work_date,
                    'activity_code' => 'attendance',
                    'equipment_id'  => null,
                ],
                [
                    'shift_id'       => $att->shift_id,
                    'activity_desc'  => 'Auto from attendance (check-in/out)',
                    'hours'          => $hours,
                    'overtime_hours' => $ot,
                    'attendance_id'  => $att->id,
                ]
            );

            // 4) OT status logic (inline di timesheets)
            if ($ot > 0) {
                // kalau belum approved, set pending
                if ($ts->ot_status !== 'approved') {
                    $ts->ot_status = 'pending';
                    $ts->ot_reason = $ts->ot_reason ?: 'Auto from attendance (>8h)';
                }
            } else {
                // tidak lembur → reset status
                $ts->ot_status      = 'none';
                $ts->ot_reason      = null;
                $ts->ot_approved_by = null;
                $ts->ot_approved_at = null;
            }
            $ts->save();
        });

        return back()->with('ok', 'Check-Out berhasil'.($dist>$radius ? ' (Di luar jangkauan)' : ''));
    }
}
