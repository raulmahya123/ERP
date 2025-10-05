<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CommoditySeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('commodities')) return;

        $now = now();
        $commodities = [
            ['code' => 'NI',   'name' => 'Nickel'],
            ['code' => 'COAL', 'name' => 'Coal'],
        ];

        foreach ($commodities as $c) {
            $existing = DB::table('commodities')->where('code', $c['code'])->first();

            if ($existing) {
                // ✅ JANGAN sentuh 'id' (PK)
                DB::table('commodities')
                    ->where('id', $existing->id)
                    ->update([
                        'name'       => $c['name'],
                        'updated_at' => $now,
                    ]);
            } else {
                // ✅ Saat insert baru, set 'id'
                DB::table('commodities')->insert([
                    'id'         => (string) Str::uuid(),
                    'code'       => $c['code'],
                    'name'       => $c['name'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
