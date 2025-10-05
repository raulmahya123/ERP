<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssetSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasTable('assets')) return;

        $now = now();

        // Ambil site default (SUL-NI kalau ada, fallback site pertama)
        $site = DB::table('sites')->where('code','SUL-NI')->first();
        if (!$site) {
            $site = DB::table('sites')->first();
            if (!$site) return; // belum ada site
        }

        // Helper: ambil master_record.id by entity+code
        $mrId = function (string $entity, ?string $code) {
            if (!$code) return null;
            return DB::table('master_records')
                ->where('entity', $entity)
                ->where('code', $code)
                ->value('id');
        };

        $catHME = $mrId('asset_categories','AST-HME');
        $catVHC = $mrId('asset_categories','AST-VHC');
        $ccPlant = $mrId('cost_centers','CC-PLANT');
        $ccProd  = $mrId('cost_centers','CC-PROD');

        $assets = [
            [
                'code' => 'EX-ZX870-01',
                'name' => 'Excavator Hitachi ZX870 #01',
                'brand'=> 'Hitachi', 'model' => 'ZX870',
                'serial_no' => 'ZX870-001',
                'status' => 'active',
                'asset_category_id' => $catHME,
                'cost_center_id'    => $ccPlant,
                'commissioned_at'   => '2019-06-01',
                'acq_cost'          => 15000000000,
                'acq_date'          => '2019-05-15',
                'extra'             => ['bucket_m3'=>4.5],
            ],
            [
                'code' => 'DT-773E-01',
                'name' => 'Dump Truck CAT 773E #01',
                'brand'=> 'Caterpillar', 'model' => '773E',
                'serial_no' => '773E-001',
                'status' => 'active',
                'asset_category_id' => $catHME,
                'cost_center_id'    => $ccProd,
                'commissioned_at'   => '2018-04-01',
                'acq_cost'          => 12000000000,
                'acq_date'          => '2018-03-10',
                'extra'             => ['capacity_ton'=>60],
            ],
            [
                'code' => 'LV-HILUX-01',
                'name' => 'Toyota Hilux 4x4',
                'brand'=> 'Toyota', 'model' => 'Hilux',
                'plate_no' => 'DD 1234 XX',
                'status' => 'active',
                'asset_category_id' => $catVHC,
                'cost_center_id'    => $ccPlant,
                'commissioned_at'   => '2020-01-12',
                'acq_cost'          => 450000000,
                'acq_date'          => '2019-12-20',
                'extra'             => ['fuel'=>'diesel'],
            ],
        ];

        foreach ($assets as $a) {
            DB::table('assets')->updateOrInsert(
                ['site_id' => $site->id, 'code' => $a['code']],
                [
                    'id'                 => (string) Str::uuid(),
                    'site_id'            => $site->id,
                    'name'               => $a['name'],
                    'brand'              => $a['brand'] ?? null,
                    'model'              => $a['model'] ?? null,
                    'serial_no'          => $a['serial_no'] ?? null,
                    'plate_no'           => $a['plate_no'] ?? null,
                    'engine_no'          => $a['engine_no'] ?? null,
                    'frame_no'           => $a['frame_no'] ?? null,
                    'status'             => $a['status'] ?? 'active',
                    'commissioned_at'    => $a['commissioned_at'] ?? null,
                    'location'           => $a['location'] ?? null,
                    'assigned_to_user_id'=> null,
                    'acq_cost'           => $a['acq_cost'] ?? null,
                    'acq_date'           => $a['acq_date'] ?? null,
                    'asset_category_id'  => $a['asset_category_id'] ?? null,
                    'cost_center_id'     => $a['cost_center_id'] ?? null,
                    'extra'              => !empty($a['extra']) ? json_encode($a['extra'], JSON_UNESCAPED_UNICODE) : null,
                    'created_by'         => DB::table('users')->value('id'),
                    'created_at'         => $now,
                    'updated_at'         => $now,
                ]
            );
        }
    }
}
