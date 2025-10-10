<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class EmploymentContractSeeder extends Seeder
{
    public function run(): void
    {
        $users = DB::table('users')->pluck('id')->all();
        $sites = DB::table('sites')->pluck('id')->all();
        $types = ['permanent','contract','outsourced']; // ⬅️ sesuai enum migration

        $rows = [];

        foreach ($sites as $sid) {
            foreach ($users as $uid) {
                $type  = $types[array_rand($types)];
                $start = Carbon::now()->subMonths(rand(1,12))->startOfDay()->toDateString();

                // permanen umumnya tanpa end_date
                $end   = $type === 'permanent'
                       ? null
                       : Carbon::parse($start)->addMonths(rand(6,18))->toDateString();

                $rows[] = [
                    'id'          => (string) Str::uuid(),
                    'site_id'     => (string) $sid,
                    'user_id'     => (string) $uid,
                    'type'        => $type,                       // ⬅️ ganti dari contract_type
                    'vendor_name' => $type === 'outsourced' ? 'PT Mitra Andalan' : null,
                    'position'    => null,
                    'base_salary' => null,
                    'start_date'  => $start,
                    'end_date'    => $end,
                    'meta'        => json_encode(['remarks' => 'Seed kontrak kerja']),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
        }

        // Jika kamu pakai unique index (mis. user_id + start_date), gunakan upsert agar id baru tidak menabrak
        DB::table('employment_contracts')->upsert(
            $rows,
            ['user_id','start_date','site_id'],            // kolom penentu unik (ubah sesuai index kamu)
            ['type','vendor_name','position','base_salary','end_date','meta','updated_at']
        );

        $this->command->info('✅ EmploymentContractSeeder selesai.');
    }
}
