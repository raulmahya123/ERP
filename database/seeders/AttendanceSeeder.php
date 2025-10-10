<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    private function getColType(string $table, string $column): ?string
    {
        // Ambil DATA_TYPE dari information_schema (e.g. datetime, time, timestamp)
        $sql = "
            SELECT DATA_TYPE
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = ?
              AND COLUMN_NAME = ?
            LIMIT 1
        ";
        $row = DB::selectOne($sql, [$table, $column]);
        return $row->DATA_TYPE ?? null;
    }

    private function fmt(string $dataType, Carbon $dt): string
    {
        $t = strtolower($dataType);
        if (str_contains($t, 'datetime') || str_contains($t, 'timestamp')) {
            return $dt->format('Y-m-d H:i:s');
        }
        if (str_contains($t, 'time')) {
            return $dt->format('H:i:s');
        }
        return $dt->format('Y-m-d H:i:s'); // fallback aman
    }

    public function run(): void
    {
        if (!Schema::hasTable('attendances')) {
            $this->command?->warn('Lewati AttendanceSeeder: tabel attendances belum ada.');
            return;
        }

        $siteIds = DB::table('sites')->pluck('id');
        $userIds = DB::table('users')->pluck('id');

        if ($siteIds->isEmpty() || $userIds->isEmpty()) {
            $this->command?->warn('Lewati AttendanceSeeder: site/user masih kosong.');
            return;
        }

        // Deteksi tipe kolom waktu
        $inType  = $this->getColType('attendances','check_in_at')  ?? 'datetime';
        $outType = $this->getColType('attendances','check_out_at') ?? 'datetime';

        foreach ($siteIds as $siteId) {
            // start_at bisa TIME/DATETIME; kita treat sebagai string waktu
            $shiftStarts = DB::table('shifts')
                ->where('site_id', $siteId)
                ->pluck('start_at','id'); // [shift_id => '06:00:00' / '2025-10-10 06:00:00']

            if ($shiftStarts->isEmpty()) continue;

            $days = collect(range(0,4))->map(fn($i)=>Carbon::now()->subDays($i)->toDateString());

            foreach ($days as $d) {
                foreach ($userIds as $userId) {
                    $shiftId = $shiftStarts->keys()->random();
                    $startStr = (string) $shiftStarts[$shiftId];

                    // Normalisasi start shift → Carbon
                    $start = str_contains($startStr, ':') && strlen($startStr) <= 8
                        ? Carbon::parse($d.' '.$startStr) // 'HH:MM:SS'
                        : Carbon::parse($startStr);        // sudah datetime

                    $in  = (clone $start)->addMinutes(rand(-5,25));
                    $out = (clone $in)->addHours(8)->addMinutes(rand(-10,30));

                    $late = max(0, $start->diffInMinutes($in, false) * -1);
                    $flags = [];
                    if ($late >= 10) $flags[] = 'late';
                    if (rand(0,10) > 8) $flags[] = 'overtime_high';

                    DB::table('attendances')->updateOrInsert(
                        ['site_id'=>$siteId, 'user_id'=>$userId, 'work_date'=>$d],
                        [
                            'id'                    => (string) Str::uuid(),
                            'shift_id'              => $shiftId,
                            'source'                => 'manual', // ENUM wajib
                            'check_in_at'           => $this->fmt($inType,  $in),
                            'check_out_at'          => $this->fmt($outType, $out),
                            'gps_in_lat'            => null,
                            'gps_in_lng'            => null,
                            'gps_out_lat'           => null,
                            'gps_out_lng'           => null,
                            'device_id'             => null,
                            'late_minutes'          => $late,
                            'early_leave_minutes'   => 0,
                            'overtime_minutes'      => rand(0,1) ? rand(0,120) : 0,
                            'work_minutes'          => $in->diffInMinutes($out),
                            'status'                => 'present',
                            'flags'                 => json_encode($flags, JSON_UNESCAPED_UNICODE),
                            'created_at'            => now(),
                            'updated_at'            => now(),
                        ]
                    );
                }
            }
        }

        $this->command?->info('✅ AttendanceSeeder: fix MariaDB SHOW COLUMNS & format DATETIME, set enum `source`.');
    }
}
