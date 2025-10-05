<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SiteConfigSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('site_configs') || !Schema::hasTable('sites') || !Schema::hasTable('commodities')) return;

        $now   = now();
        $sites = DB::table('sites')->get(['id','code']);
        $commodities = DB::table('commodities')->get(['id','code']);

        foreach ($sites as $site) {
            foreach ($commodities as $com) {
                DB::table('site_configs')->updateOrInsert(
                    ['site_id' => $site->id, 'commodity_id' => $com->id],
                    [
                        'id'         => (string) Str::uuid(),
                        'params'     => json_encode([
                            'hba' => $com->code === 'COAL' ? 75 : null,
                            'ni_grade_min' => $com->code === 'NI' ? 1.6 : null,
                            'assay_method' => $com->code === 'NI' ? 'AAS' : 'Lab Std',
                            'shift_roster' => ['D1','D2','N1','N2','OFF'],
                        ], JSON_UNESCAPED_UNICODE),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }
        }
    }
}
