<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SiteSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $sites = [
            ['code' => 'SUL-NI',    'name' => 'Sulawesi - Nickel'],
            ['code' => 'KALSEL-CO', 'name' => 'Kalimantan Selatan - Coal'],
        ];

        foreach ($sites as $s) {
            // Cek apakah sudah ada berdasarkan 'code'
            $existing = DB::table('sites')->where('code', $s['code'])->first();

            if ($existing) {
                // ✅ Update hanya kolom non-PK
                DB::table('sites')->where('id', $existing->id)->update([
                    'name'       => $s['name'],
                    'updated_at' => $now,
                ]);
            } else {
                // ✅ Insert baru dengan id UUID
                DB::table('sites')->insert([
                    'id'         => (string) Str::uuid(),
                    'code'       => $s['code'],
                    'name'       => $s['name'],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
