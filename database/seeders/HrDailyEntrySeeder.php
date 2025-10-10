<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class HrDailyEntrySeeder extends Seeder
{
    public function run(): void
    {
        $sites = DB::table('sites')->pluck('id')->all();
        $users = DB::table('users')->pluck('id')->all();
        $types = ['leave','permit','sick','shift_change'];

        $today = Carbon::today()->toDateString();
        $rows  = [];

        foreach ($sites as $sid) {
            foreach ($users as $i => $uid) {
                if ($i % 5 !== 0) continue;

                $type = $types[array_rand($types)];

                $rows[] = [
                    'id'            => (string) Str::uuid(),
                    'site_id'       => (string) $sid,
                    'user_id'       => (string) $uid,
                    'date'          => $today,                 // ⬅️ kolom sesuai migration
                    'type'          => $type,
                    'code'          => null,                   // contoh: null (lihat catatan di bawah)
                    'reason'        => 'Seed HR entry',
                    'from_shift_id' => $type==='shift_change' ? (string) Str::uuid() : null,
                    'to_shift_id'   => $type==='shift_change' ? (string) Str::uuid() : null,
                    'meta'          => json_encode(['seed'=>true]),
                    'created_at'    => now(),
                    'updated_at'    => now(),
                ];
            }
        }

        // Upsert berdasarkan unique index di tabel
        DB::table('hr_daily_entries')->upsert(
            $rows,
            ['site_id','user_id','date','type','code'], // unique-by
            ['reason','from_shift_id','to_shift_id','meta','updated_at'] // kolom yang di-update
        );

        $this->command->info('✅ HrDailyEntrySeeder selesai — data cuti/izin/sakit/shift_change dibuat.');
    }
}
