<?php

namespace App\Jobs;

use App\Models\Attendance;
use App\Notifications\AttendanceAnomalyNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class DetectAttendanceAnomalyJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(public string $attendanceId) {}

    public function handle(): void
    {
        $a = Attendance::with('user')->find($this->attendanceId);
        if (!$a) return;

        $flags = $a->flags ?? [];
        $isAbnormal = count(array_intersect($flags, ['late', 'overtime_high', 'no_checkout', 'abnormal'])) > 0;

        if ($isAbnormal) {
            $supervisors = \App\Models\User::query()
                ->where('division_id', $a->user->division_id)
                ->whereIn('role', ['manager', 'supervisor', 'foreman'])
                ->take(5)
                ->get();

            foreach ($supervisors as $u) {
                $u->notify(new AttendanceAnomalyNotification($a));
            }
        }
    }
}
