<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class TimesheetSeeder extends Seeder
{
    public function run(): void
    {
        $sites = DB::table('sites')->get(['id']);
        $users = DB::table('users')->get(['id']);
        $acts  = ['hauling','loading','dozing','fueling','maintenance'];

        foreach ($sites as $site) {
            $days = collect(range(0,4))->map(fn($i)=>Carbon::now()->subDays($i)->toDateString());
            foreach ($days as $d) {
                foreach ($users as $u) {
                    $activity = $acts[array_rand($acts)];
                    DB::table('timesheets')->updateOrInsert(
                        ['site_id'=>$site->id,'user_id'=>$u->id,'work_date'=>$d,'activity_code'=>$activity],
                        [
                            'id'             => (string) Str::uuid(),
                            'hours'          => rand(6,9),
                            'overtime_hours' => rand(0,2),
                            'activity_desc'  => ucfirst($activity),
                            'created_at'=>now(),
                            'updated_at'=>now(),
                        ]
                    );
                }
            }
        }

        $this->command->info('✅ TimesheetSeeder selesai — data timesheet dibuat.');
    }
}
