<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Helper: panggil seeder hanya jika semua tabel yang dibutuhkan tersedia.
     * - Contoh: callIfTablesExist(['sites','pits'], PitSeeder::class);
     */
    private function callIfTablesExist(array $tables, string $seederClass): void
    {
        $missing = collect($tables)->reject(fn ($t) => Schema::hasTable($t))->values();
        if ($missing->isNotEmpty()) {
            $this->command?->warn("Lewati {$seederClass}: tabel belum ada -> ".implode(', ', $missing->all()));
            return;
        }
        $this->call($seederClass);
    }

    public function run(): void
    {
        // ===== 0) Fundamental (role/division/user) =====
        $this->callIfTablesExist(['roles'], RoleSeeder::class);
        $this->callIfTablesExist(['divisions'], DivisionSeeder::class);
        $this->callIfTablesExist(['users','roles','divisions'], UserSeeder::class);

        // ===== 1) Master site & commodity =====
        $this->callIfTablesExist(['sites'],       SiteSeeder::class);
        $this->callIfTablesExist(['commodities'], CommoditySeeder::class);

        // ===== 2) Master Entities & Records =====
        $this->callIfTablesExist(
            ['master_entities','master_records','users','sites'],
            MasterDataSeeder::class
        );

        // ===== 3) Permissions utk master_records =====
        $this->callIfTablesExist(
            ['master_record_permissions','master_records','users','roles'],
            MasterRecordAccessSeeder::class
        );

        // ===== 4) Site Configs (opsional) =====
        $this->callIfTablesExist(['site_configs','sites'], SiteConfigSeeder::class);

        // ===== 5) Assets (opsional contoh) =====
        $this->callIfTablesExist(['assets','sites','master_records'], AssetSeeder::class);

        // ===== 6) Set default_site_id (jika kolomnya ada) =====
        if (Schema::hasTable('users') && Schema::hasTable('sites') && Schema::hasColumn('users','default_site_id')) {
            $firstSiteId = DB::table('sites')->orderBy('name')->value('id');
            if ($firstSiteId) {
                DB::table('users')->whereNull('default_site_id')->update(['default_site_id' => $firstSiteId]);
                $this->command?->info("default_site_id di-set ke site pertama untuk user yang kosong.");
            }
        } else {
            $this->command?->warn("Lewati set default_site_id: tabel/kolom belum ada.");
        }

        // ===== 7) HCM & Manpower (fase-2) =====
        $this->callIfTablesExist(['shifts','sites'], ShiftSeeder::class);
        $this->callIfTablesExist(['shift_rosters','shifts','users','sites'], ShiftRosterSeeder::class);
        $this->callIfTablesExist(['attendances','shifts','users','sites'],   AttendanceSeeder::class);
        $this->callIfTablesExist(['timesheets','users','sites'],             TimesheetSeeder::class);
        $this->callIfTablesExist(['hr_daily_entries','users','sites'],       HrDailyEntrySeeder::class);
        $this->callIfTablesExist(['manpower_plans','sites'],                 ManpowerPlanSeeder::class);
        $this->callIfTablesExist(['employment_contracts','users','sites'],   EmploymentContractSeeder::class);
        $this->callIfTablesExist(['manpower_realizations','manpower_plans','sites'], ManpowerRealizationSeeder::class);
        $this->callIfTablesExist(['crew_assignments','users','sites'],       CrewAssignmentSeeder::class);

        // ===== 8) SCM basic masters =====
        // PITS bergantung ke 'sites' dan tentu tabel 'pits' itu sendiri.
        // Ini hanya untuk jalur "seed penuh". Kalau mau jalankan khusus PitSeeder saja, lihat perintah di bawah.
        $this->callIfTablesExist(['pits','sites'], PitSeeder::class);

        $this->command?->info('✅ Database seeding selesai.');
    }
}
