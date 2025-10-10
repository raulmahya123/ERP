<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $siteIds = DB::table('sites')->pluck('id');

        $rows = [
            ['code'=>'A','name'=>'Shift A — Pagi','start_at'=>'06:00:00','end_at'=>'14:00:00','work_minutes'=>480,'color'=>'#22c55e','remarks'=>'Pagi'],
            ['code'=>'B','name'=>'Shift B — Siang','start_at'=>'14:00:00','end_at'=>'22:00:00','work_minutes'=>480,'color'=>'#f59e0b','remarks'=>'Siang'],
            ['code'=>'C','name'=>'Shift C — Malam','start_at'=>'22:00:00','end_at'=>'06:00:00','work_minutes'=>480,'color'=>'#3b82f6','remarks'=>'Malam'],
            ['code'=>'NON','name'=>'Non Shift / Kantor','start_at'=>'08:00:00','end_at'=>'17:00:00','work_minutes'=>480,'color'=>'#6b7280','remarks'=>'Office'],
        ];

        // helper: buang key yang kolomnya tidak ada
        $filterCols = function(array $payload): array {
            $allowed = ['site_id','id','code','name','start_at','end_at','created_at','updated_at'];
            foreach (['work_minutes','color','remarks'] as $opt) {
                if (Schema::hasColumn('shifts', $opt)) $allowed[] = $opt;
            }
            return array_intersect_key($payload, array_flip($allowed));
        };

        foreach ($siteIds as $sid) {
            foreach ($rows as $r) {
                $base = array_merge($r, [
                    'id'         => (string) Str::uuid(),
                    'site_id'    => $sid,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('shifts')->updateOrInsert(
                    ['site_id' => $sid, 'code' => $r['code']],
                    $filterCols($base)
                );
            }
        }

        $this->command?->info('✅ ShiftSeeder: seeded sesuai struktur tabel (schema-aware).');
    }
}
