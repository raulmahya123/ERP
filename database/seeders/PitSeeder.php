<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PitSeeder extends Seeder
{
    public function run(): void
    {
        $siteId = DB::table('sites')->orderBy('code')->value('id');
        if (!$siteId) {
            $this->command?->warn('No site found; skipping PitSeeder.');
            return;
        }

        $rows = [
            ['code' => 'PIT-A', 'name' => 'Pit A'],
            ['code' => 'PIT-B', 'name' => 'Pit B'],
        ];

        foreach ($rows as $r) {
            DB::table('pits')->updateOrInsert(
                ['site_id' => $siteId, 'code' => $r['code']],
                [
                    'id'         => (string) Str::uuid(),
                    'name'       => $r['name'],
                    'active'     => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
