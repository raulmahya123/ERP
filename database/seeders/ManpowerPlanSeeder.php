<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ManpowerPlanSeeder extends Seeder
{
    public function run(): void
    {
        $sites = DB::table('sites')->get(['id']);
        $depts = ['Plant','SCM','Operation','HSE','Support'];
        $slots = ['A','B','C','NON'];

        foreach ($sites as $site) {
            foreach ($slots as $slot) {
                foreach ($depts as $dep) {
                    DB::table('manpower_plans')->updateOrInsert(
                        ['site_id'=>$site->id,'date'=>Carbon::now()->toDateString(),'shift_slot'=>$slot,'department'=>$dep],
                        [
                            'id' => (string) Str::uuid(),
                            'planned_headcount'=>rand(4,15),
                            'note'=>'Seed plan '.$dep,
                            'created_at'=>now(),
                            'updated_at'=>now(),
                        ]
                    );
                }
            }
        }

        $this->command->info('✅ ManpowerPlanSeeder selesai — plan per shift/dept dibuat.');
    }
}
