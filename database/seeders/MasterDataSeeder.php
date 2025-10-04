<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $now       = now();
            $creatorId = DB::table('users')->value('id'); // boleh null kalau tidak ada user

            // Pastikan tabel master_entities ada
            if (!Schema::hasTable('master_entities')) {
                throw new \RuntimeException("Table 'master_entities' belum ada. Jalankan migrasi yang menambahkan table ini dulu.");
            }

            // 1) Pastikan master_entities terisi dan ambil peta key -> id
            $entitiesCatalog = [
                'units'           => 'Units',
                'pits'            => 'Pits',
                'stockpiles'      => 'Stockpiles',
                'cost_centers'    => 'Cost Centers',
                'accounts'        => 'Accounts',
                'employees'       => 'Employees',
                'asset_categories'=> 'Asset Categories',
            ];

            $entityMap = []; // key => id
            foreach ($entitiesCatalog as $key => $label) {
                $row = DB::table('master_entities')->where('key', $key)->first();
                if (!$row) {
                    $id = (string) Str::uuid();
                    DB::table('master_entities')->insert([
                        'id'         => $id,
                        'key'        => $key,
                        'label'      => $label,
                        'enabled'    => true,
                        'sort'       => 0,
                        'schema'     => null,
                        'icon'       => null,
                        'color_from' => null,
                        'color_to'   => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                    $entityMap[$key] = $id;
                } else {
                    $entityMap[$key] = $row->id;
                }
            }

            // 2) Hapus data lama (urutkan biar FK aman)
            if (Schema::hasTable('master_record_permissions')) {
                DB::table('master_record_permissions')->delete();
            }
            DB::table('master_records')->delete();

            // 3) Data master (sama seperti punyamu)
            $units = [
                [
                    'code' => 'HT-773E-01',
                    'name' => 'Haul Truck CAT 773E #01',
                    'desc' => 'Haul truck utama untuk overburden',
                    'extra' => ['brand' => 'Caterpillar', 'capacity_ton' => 60, 'year' => 2018, 'dept' => 'Plant'],
                ],
                [
                    'code' => 'EX-ZX870-01',
                    'name' => 'Excavator Hitachi ZX870 #01',
                    'desc' => 'Excavator loading',
                    'extra' => ['brand' => 'Hitachi', 'bucket_m3' => 4.5, 'year' => 2019, 'dept' => 'Plant'],
                ],
                [
                    'code' => 'BD-D155A-01',
                    'name' => 'Bulldozer Komatsu D155A #01',
                    'desc' => 'Dozer support pit',
                    'extra' => ['brand' => 'Komatsu', 'blade' => 'Semi-U', 'year' => 2017, 'dept' => 'Plant'],
                ],
            ];

            $pits = [
                ['code' => 'PIT-A', 'name' => 'Pit A', 'desc' => 'Main nickel pit', 'extra' => ['coords' => [122.12, -3.45], 'bench' => '50-70m']],
                ['code' => 'PIT-B', 'name' => 'Pit B', 'desc' => 'Cadangan',        'extra' => ['coords' => [122.18, -3.48], 'bench' => '30-45m']],
            ];

            $stockpiles = [
                ['code' => 'SP-1', 'name' => 'Stockpile 1', 'desc' => 'ROM Nickel', 'extra' => ['capacity_ton' => 100000, 'cover' => 'open']],
                ['code' => 'SP-2', 'name' => 'Stockpile 2', 'desc' => 'Coal yard',  'extra' => ['capacity_ton' => 50000,  'cover' => 'partial']],
            ];

            $costCenters = [
                ['code' => 'CC-PLANT', 'name' => 'Plant',      'desc' => 'Perawatan & operasi alat',         'extra' => ['owner_division' => 'plant']],
                ['code' => 'CC-PROD',  'name' => 'Production', 'desc' => 'Operasional produksi',             'extra' => ['owner_division' => 'production']],
                ['code' => 'CC-SHE',   'name' => 'SHE',        'desc' => 'Safety, Health, Environment',      'extra' => ['owner_division' => 'she']],
                ['code' => 'CC-HRGA',  'name' => 'HRGA',       'desc' => 'HR & General Affairs',             'extra' => ['owner_division' => 'hrga']],
                ['code' => 'CC-FIN',   'name' => 'Finance & Acc', 'desc' => 'Keuangan & akuntansi',          'extra' => ['owner_division' => 'finance']],
            ];

            $accounts = [
                ['code' => '5101', 'name' => 'Beban BBM',             'desc' => 'Consumable fuel',      'extra' => ['type' => 'expense']],
                ['code' => '5102', 'name' => 'Beban Oli & Pelumas',   'desc' => 'Lubricants & oils',    'extra' => ['type' => 'expense']],
                ['code' => '1201', 'name' => 'Piutang Usaha',         'desc' => 'Accounts Receivable',  'extra' => ['type' => 'asset']],
                ['code' => '1501', 'name' => 'Persediaan Suku Cadang','desc' => 'Sparepart Inventory',  'extra' => ['type' => 'asset']],
            ];

            $employees = [
                ['code' => 'EMP-0001', 'name' => 'Budi Santoso',   'desc' => 'Operator Haul Truck', 'extra' => ['nik' => '3173xxxx', 'division' => 'Plant',       'position' => 'Operator']],
                ['code' => 'EMP-0002', 'name' => 'Sari Wulandari', 'desc' => 'Supervisor Pit A',    'extra' => ['nik' => '7371xxxx', 'division' => 'Production',  'position' => 'Supervisor']],
                ['code' => 'EMP-0003', 'name' => 'Andi Prasety  o',  'desc' => 'Mechanic',            'extra' => ['nik' => '7372xxxx', 'division' => 'Plant',       'position' => 'Mechanic']],
            ];

            $assetCategories = [
                ['code' => 'AST-HME', 'name' => 'Heavy Mining Equipment', 'desc' => 'Excavator, Dozer, Dump Truck', 'extra' => ['depr_method' => 'SL', 'useful_life_year' => 8]],
                ['code' => 'AST-VHC', 'name' => 'Vehicle',                 'desc' => 'Light vehicle, pickup',        'extra' => ['depr_method' => 'SL', 'useful_life_year' => 5]],
                ['code' => 'AST-IT',  'name' => 'IT Equipment',            'desc' => 'Laptop, server, network',      'extra' => ['depr_method' => 'SL', 'useful_life_year' => 3]],
            ];

            // 4) Helper insert yang menyertakan master_entity_id
            $insertMany = function (string $entityKey, array $rows) use ($creatorId, $now, $entityMap) {
                if (empty($rows)) return;

                $mid = $entityMap[$entityKey] ?? null;
                if (!$mid) {
                    throw new \RuntimeException("master_entities.key '{$entityKey}' tidak ditemukan.");
                }

                $payload = [];
                foreach ($rows as $r) {
                    $payload[] = [
                        'id'                => (string) Str::uuid(),
                        'master_entity_id'  => $mid,                // ⬅️ WAJIB
                        'entity'            => $entityKey,          // masih disimpan untuk kemudahan filter
                        'name'              => $r['name'],
                        'code'              => $r['code'] ?? null,
                        'description'       => $r['desc'] ?? null,
                        'extra'             => !empty($r['extra']) ? json_encode($r['extra']) : null,
                        'created_by'        => $creatorId,          // boleh null (kolom dibuat nullable)
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ];
                }
                DB::table('master_records')->insert($payload);
            };

            // 5) Eksekusi insert per entitas
            $insertMany('units',            $units);
            $insertMany('pits',             $pits);
            $insertMany('stockpiles',       $stockpiles);
            $insertMany('cost_centers',     $costCenters);
            $insertMany('accounts',         $accounts);
            $insertMany('employees',        $employees);
            $insertMany('asset_categories', $assetCategories);

            // 6) (Opsional) Seed permission default untuk manager
            if (Schema::hasTable('master_record_permissions')) {
                $managers = DB::table('users')
                    ->leftJoin('roles', 'roles.id', '=', 'users.role_id')
                    ->where(function($q){
                        $q->where('roles.key','manager')
                          ->orWhere('roles.name','like','%manager%');
                    })
                    ->get(['users.id as uid']);

                if ($managers->count() > 0) {
                    $records = DB::table('master_records')->pluck('id');

                    $chunk = [];
                    foreach ($managers as $m) {
                        foreach ($records as $rid) {
                            $chunk[] = [
                                'id'               => (string) Str::uuid(),
                                'master_record_id' => $rid,
                                'user_id'          => $m->uid,
                                'can_view'         => true,
                                'can_download'     => false,
                                'can_update'       => false,
                                'can_delete'       => false,
                                'created_at'       => $now,
                                'updated_at'       => $now,
                            ];
                            if (count($chunk) === 1000) {
                                DB::table('master_record_permissions')->insert($chunk);
                                $chunk = [];
                            }
                        }
                    }
                    if (!empty($chunk)) {
                        DB::table('master_record_permissions')->insert($chunk);
                    }
                }
            }
        });
    }
}
