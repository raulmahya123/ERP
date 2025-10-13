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

        // List kolom yang ada di tabel attendances agar aman saat insert
        $columns = Schema::getColumnListing('attendances');
        $has = fn(string $col) => in_array($col, $columns, true);

        // Deteksi tipe kolom waktu
        $inType  = $this->getColType('attendances','check_in_at')  ?? 'datetime';
        $outType = $this->getColType('attendances','check_out_at') ?? 'datetime';

        foreach ($siteIds as $siteId) {
            // start_at bisa TIME/DATETIME; treat sebagai string waktu
            $shiftStarts = DB::table('shifts')
                ->where('site_id', $siteId)
                ->pluck('start_at','id'); // [shift_id => '06:00:00' / '2025-10-10 06:00:00']

            if ($shiftStarts->isEmpty()) continue;

            $days = collect(range(0,4))->map(fn($i)=>Carbon::now()->subDays($i)->toDateString());

            foreach ($days as $d) {
                foreach ($userIds as $userId) {
                    $shiftId  = $shiftStarts->keys()->random();
                    $startStr = (string) $shiftStarts[$shiftId];

                    // Normalisasi start shift → Carbon
                    $start = (str_contains($startStr, ':') && strlen($startStr) <= 8)
                        ? Carbon::parse($d.' '.$startStr) // 'HH:MM:SS'
                        : Carbon::parse($startStr);        // sudah datetime

                    $in  = (clone $start)->addMinutes(rand(-5,25));
                    $out = (clone $in)->addHours(8)->addMinutes(rand(-10,30));

                    // Keterlambatan (menit)
                    $late = max(0, $start->diffInMinutes($in, false) * -1);

                    // Flag acak
                    $flags = [];
                    if ($late >= 10) $flags[] = 'late';
                    if (rand(0,10) > 8) $flags[] = 'overtime_high';

                    // Payload minimal (yang hampir pasti ada)
                    $payload = [
                        'id'          => (string) Str::uuid(),
                        'shift_id'    => $shiftId,
                        'check_in_at' => $this->fmt($inType,  $in),
                        'check_out_at'=> $this->fmt($outType, $out),
                        'updated_at'  => now(),
                        'created_at'  => now(),
                    ];

                    // Kolom opsional → hanya isi jika ada di tabel
                    $optional = [
                        'source'               => 'manual',
                        'gps_in_lat'           => null,
                        'gps_in_lng'           => null,
                        'gps_out_lat'          => null,
                        'gps_out_lng'          => null,
                        'device_id'            => null,
                        'late_minutes'         => $late,
                        'early_leave_minutes'  => 0,
                        'overtime_minutes'     => rand(0,1) ? rand(0,120) : 0,
                        'work_minutes'         => $in->diffInMinutes($out),
                        'status'               => 'present',
                        'flags'                => json_encode($flags, JSON_UNESCAPED_UNICODE),
                    ];

                    foreach ($optional as $col => $val) {
                        if ($has($col)) {
                            $payload[$col] = $val;
                        }
                    }

                    // Kriteria unik baris (agar update jika sudah ada)
                    $where = [
                        'site_id'   => $siteId,
                        'user_id'   => $userId,
                        'work_date' => $d,
                    ];

                    // Pastikan ketiga kolom kriteria ada; kalau tidak ada, pakai kombinasi yang tersedia
                    $criteria = [];
                    foreach ($where as $k => $v) {
                        if ($has($k)) $criteria[$k] = $v;
                        else $payload[$k] = $v; // kalau kolomnya tidak ada, masukkan sebagai nilai biasa (aman)
                    }

                    // Simpan
                    DB::table('attendances')->updateOrInsert($criteria, $payload);
                }
            }
        }

        $this->command?->info('✅ AttendanceSeeder: hanya mengisi kolom yang ada, aman dari error kolom hilang (incl. overtime_minutes).');
    }
}
