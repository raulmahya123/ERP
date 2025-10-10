<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ShiftRosterSeeder extends Seeder
{
    public function run(): void
    {
        $sites = DB::table('sites')->get(['id']);
        $users = DB::table('users')->get(['id']);
        $days  = collect(range(0, 4))->map(fn($i)=>Carbon::now()->subDays($i)->toDateString());

        foreach ($sites as $site) {
            $shifts = DB::table('shifts')->where('site_id',$site->id)->pluck('id','code');

            foreach ($days as $d) {
                foreach ($users as $i => $u) {
                    $code = ['A','B','C','NON'][$i % 4];
                    DB::table('shift_rosters')->updateOrInsert(
                        ['site_id'=>$site->id,'user_id'=>$u->id,'roster_date'=>$d],
                        [
                            'id'        => (string) Str::uuid(),
                            'shift_id'  => $shifts[$code] ?? null,
                            'crew_code' => 'CRW-'.str_pad($i+1,3,'0',STR_PAD_LEFT),
                            'remarks'   => 'Seed roster '.$code,
                            'created_at'=> now(),
                            'updated_at'=> now(),
                        ]
                    );
                }
            }
        }

        $this->command->info('✅ ShiftRosterSeeder selesai — roster 5 hari terakhir dibuat.');
    }
}
