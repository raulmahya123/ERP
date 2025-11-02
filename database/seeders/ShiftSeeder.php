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

        // Kolom opsional tergantung schema
        $optionalCols = collect(['work_minutes','color','remarks'])
            ->filter(fn($c) => Schema::hasColumn('shifts', $c))
            ->values()
            ->all();

        foreach ($siteIds as $sid) {
            foreach ($rows as $r) {
                // cari by (site_id, code)
                $existing = DB::table('shifts')
                    ->where('site_id', $sid)
                    ->where('code', $r['code'])
                    ->first();

                // kolom umum untuk update
                $updateCols = array_merge([
                    'name'       => $r['name'],
                    'start_at'   => $r['start_at'],
                    'end_at'     => $r['end_at'],
                    'updated_at' => now(),
                ], array_intersect_key($r, array_flip($optionalCols)));

                if ($existing) {
                    // UPDATE: jangan ubah 'id' & 'created_at'
                    DB::table('shifts')
                        ->where('id', $existing->id)
                        ->update($updateCols);
                } else {
                    // INSERT: boleh set 'id'
                    $insertCols = array_merge([
                        'id'         => (string) Str::uuid(),
                        'site_id'    => $sid,
                        'code'       => $r['code'],
                        'name'       => $r['name'],
                        'start_at'   => $r['start_at'],
                        'end_at'     => $r['end_at'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ], array_intersect_key($r, array_flip($optionalCols)));

                    DB::table('shifts')->insert($insertCols);
                }
            }
        }

        $this->command?->info('✅ ShiftSeeder: done tanpa mengubah primary key.');
    }
}
