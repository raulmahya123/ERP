<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    /**
     * Helper: panggil seeder hanya jika semua tabel yang dibutuhkan tersedia.
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
        // ====== 0) Seeders fundamental: Role, Division, User ======
        // Roles & Divisions dulu supaya UserSeeder bisa set role_id & division_id.
        $this->callIfTablesExist(['roles'],        RoleSeeder::class);
        $this->callIfTablesExist(['divisions'],    DivisionSeeder::class);
        $this->callIfTablesExist(['users','roles','divisions'], UserSeeder::class);

        // ====== 1) Sites & Commodities (dipakai banyak module) ======
        // Sites harus ada sebelum MasterDataSeeder (karena ada site_id di master_records)
        $this->callIfTablesExist(['sites'],        SiteSeeder::class);
        // Opsional, kalau kamu punya tabel komoditas:
        $this->callIfTablesExist(['commodities'],  CommoditySeeder::class);

        // ====== 2) Master Entities + Master Records ======
        // Butuh: master_entities, master_records, users (created_by), sites (site_id).
        $this->callIfTablesExist(
            ['master_entities','master_records','users','sites'],
            MasterDataSeeder::class
        );

        // ====== 3) Permissions utk master_records ======
        // Butuh: master_record_permissions, master_records, users, roles
        $this->callIfTablesExist(
            ['master_record_permissions','master_records','users','roles'],
            MasterRecordAccessSeeder::class
        );

        // ====== 4) Site Configs (opsional) ======
        // Mis. tabel site_configs & commodities
        $this->callIfTablesExist(['site_configs','sites'], SiteConfigSeeder::class);

        // ====== 5) Assets (opsional contoh) ======
        // Biasanya butuh sites + master_records (asset_categories/units)
        $this->callIfTablesExist(['assets','sites','master_records'], AssetSeeder::class);

        // ====== 6) Set default_site_id utk semua user (jika kolomnya ada) ======
        if (Schema::hasTable('users') && Schema::hasTable('sites') && Schema::hasColumn('users','default_site_id')) {
            $firstSiteId = DB::table('sites')->orderBy('name')->value('id');
            if ($firstSiteId) {
                DB::table('users')->whereNull('default_site_id')->update(['default_site_id' => $firstSiteId]);
                $this->command?->info("default_site_id di-set ke site pertama untuk user yang kosong.");
            }
        } else {
            $this->command?->warn("Lewati set default_site_id: tabel/kolom belum ada.");
        }

        $this->command?->info('✅ Database seeding selesai.');
    }
}
