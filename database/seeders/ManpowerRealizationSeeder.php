<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\Site;

class ManpowerRealizationSeeder extends Seeder
{
    /**
     * Jalankan seeder untuk mengisi data dummy manpower_realizations
     */
    public function run(): void
    {
        // Ambil semua site yang ada (atau buat dummy 1 kalau kosong)
        $sites = Site::all();
        if ($sites->isEmpty()) {
            $dummySiteId = (string) Str::uuid();
            DB::table('sites')->insert([
                'id' => $dummySiteId,
                'name' => 'Site Dummy',
                'code' => 'DUM',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $sites = collect([(object) ['id' => $dummySiteId]]);
        }

        // Konfigurasi departemen & shift
        $departments = ['Production', 'Maintenance', 'HSE', 'HRGA'];
        $shifts = ['A', 'B', 'C', 'NON'];

        // Range tanggal 7 hari terakhir
        $dates = collect(range(0, 6))->map(fn ($i) => Carbon::now()->subDays($i)->toDateString());

        $records = [];

        foreach ($sites as $site) {
            foreach ($dates as $date) {
                foreach ($departments as $dept) {
                    foreach ($shifts as $shift) {
                        $headcount = rand(15, 45);

                        $records[] = [
                            'id' => (string) Str::uuid(),
                            'site_id' => $site->id,
                            'date' => $date,
                            'shift_slot' => $shift,
                            'department' => $dept,
                            'actual_headcount' => $headcount,
                            'actual_operators' => rand(5, 20),
                            'actual_mechanics' => rand(2, 10),
                            'actual_helpers' => rand(1, 5),
                            'actual_others' => rand(0, 5),
                            'production_tonnage' => round(rand(1000, 4000) * 1.0, 2),
                            'manhours' => round($headcount * rand(7, 12), 2),
                            'meta' => json_encode([
                                'note' => 'auto generated seed',
                                'supervisor' => fake()->name(),
                            ]),
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                }
            }
        }

        DB::table('manpower_realizations')->insert($records);

        $this->command->info('✅ ManpowerRealizationSeeder berhasil menambahkan ' . count($records) . ' baris data.');
    }
}
