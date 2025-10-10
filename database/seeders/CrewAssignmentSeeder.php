<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CrewAssignmentSeeder extends Seeder
{
    public function run(): void
    {
        $sites = DB::table('sites')->get(['id']);
        $users = DB::table('users')->get(['id']);
        $roles = ['operator','driver','helper','mechanic','welder'];
        $acts  = ['hauling','loading','maintenance','fueling','welding'];
        $slots = ['A','B','C','NON'];
        $date  = Carbon::now()->toDateString();

        foreach ($sites as $site) {
            foreach ($users as $i => $u) {
                $slot = $slots[$i % count($slots)];
                DB::table('crew_assignments')->updateOrInsert(
                    ['site_id'=>$site->id,'date'=>$date,'user_id'=>$u->id,'shift_slot'=>$slot],
                    [
                        'id'            => (string) Str::uuid(),
                        'role'          => $roles[$i % count($roles)],
                        'activity_code' => $acts[$i % count($acts)],
                        'remarks'       => 'Seed crew '.$slot,
                        'created_at'=>now(),
                        'updated_at'=>now(),
                    ]
                );
            }
        }

        $this->command->info('✅ CrewAssignmentSeeder selesai — mapping crew terbuat.');
    }
}
