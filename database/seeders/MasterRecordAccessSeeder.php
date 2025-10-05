<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MasterRecordAccessSeeder extends Seeder
{
    public function run(): void
    {
        // ===== 0) Safety: pastikan tabel ada =====
        $schema = DB::getSchemaBuilder();
        foreach (['master_records', 'master_record_permissions', 'users', 'roles'] as $t) {
            if (!$schema->hasTable($t)) {
                $this->command?->warn("Table '{$t}' tidak ditemukan. Lewati seeding akses.");
                return;
            }
        }

        // ===== 1) Cek composite-unique utk upsert (master_record_id, user_id) =====
        try {
            $hasUnique = DB::selectOne("
                SELECT 1
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                  AND table_name  = 'master_record_permissions'
                  AND index_name  = 'uniq_master_record_user'
                  AND NON_UNIQUE  = 0
                LIMIT 1
            ");
            if (!$hasUnique) {
                $this->command?->warn(
                    "Unique index 'uniq_master_record_user' tidak ditemukan. ".
                    "Tambahkan di migrasi: \$t->unique(['master_record_id','user_id'], 'uniq_master_record_user');"
                );
            }
        } catch (\Throwable $e) {
            // abaikan kalau information_schema dibatasi
        }

        // ===== 2) Matrix Role → Entity → Permissions =====
        $matrix = [
            'gm' => [
                '*' => ['can_view'=>true, 'can_download'=>true, 'can_update'=>true, 'can_delete'=>true],
            ],
            'manager' => [
                '*' => ['can_view'=>true, 'can_download'=>false, 'can_update'=>true, 'can_delete'=>false],
            ],
            'foreman' => [
                'pits'       => ['can_view'=>true],
                'units'      => ['can_view'=>true],
                'stockpiles' => ['can_view'=>true],
            ],
            'operator' => [
                'units' => ['can_view'=>true],
            ],
            'hse_officer' => [
                'pits'       => ['can_view'=>true],
                'stockpiles' => ['can_view'=>true],
            ],
            'hr' => [
                'employees' => ['can_view'=>true],
            ],
            'finance' => [
                'accounts'     => ['can_view'=>true, 'can_download'=>true],
                'cost_centers' => ['can_view'=>true, 'can_download'=>true],
            ],
        ];

        // ===== Helper: normalisasi role & entity =====
        $normalizeRole = function (?string $raw): string {
            $norm = Str::of((string)$raw)->lower()->replace(['_', '-'], ' ')->squish()->toString();
            $map = [
                'gm'               => 'gm',
                'general manager'  => 'gm',
                'generalmanager'   => 'gm',
                'manager'          => 'manager',
                'mgr'              => 'manager',
            ];
            return $map[$norm] ?? $norm;
        };

        $normalizeEntity = function (string $raw): string {
            $k = Str::of($raw)->lower()->replace(['_', '-'], ' ')->squish()->toString();
            $map = [
                'unit' => 'units', 'units' => 'units',
                'pit'  => 'pits',  'pits'  => 'pits',
                'stockpile' => 'stockpiles', 'stockpiles' => 'stockpiles',
                'cost center' => 'cost_centers', 'cost centers' => 'cost_centers', 'cost_centers' => 'cost_centers',
                'account' => 'accounts', 'accounts' => 'accounts',
                'employee' => 'employees', 'employees' => 'employees',
                'asset category' => 'asset_categories', 'asset categories' => 'asset_categories', 'asset_categories' => 'asset_categories',
            ];
            return $map[$k] ?? $k;
        };

        // ===== 3) Ambil USERS (COALESCE dinamis, tanpa asumsi roles.slug ada) =====
        $roleParts = [];
        if ($schema->hasColumn('roles', 'key'))       { $roleParts[] = 'roles.`key`'; }
        if ($schema->hasColumn('roles', 'slug'))      { $roleParts[] = 'roles.`slug`'; }
        if ($schema->hasColumn('roles', 'name'))      { $roleParts[] = 'roles.`name`'; }
        if ($schema->hasColumn('users', 'role'))      { $roleParts[] = 'users.`role`'; }
        if ($schema->hasColumn('users', 'role_key'))  { $roleParts[] = 'users.`role_key`'; }

        $coalesceExpr = $roleParts
            ? 'LOWER(COALESCE('.implode(',', $roleParts).'))'
            : 'NULL';

        $usersQ = DB::table('users')
            ->leftJoin('roles', 'roles.id', '=', 'users.role_id')
            ->select([
                'users.id as uid',
                DB::raw($coalesceExpr.' as role_raw'),
            ]);

        if ($schema->hasColumn('users', 'deleted_at')) {
            $usersQ->whereNull('users.deleted_at');
        }

        $users = $usersQ->get();
        if ($users->isEmpty()) {
            $this->command?->warn('Tidak ada user aktif. Seed users & roles dulu.');
            return;
        }

        // ===== 4) Ambil MASTER RECORDS & group by entity =====
        $records = DB::table('master_records')->get(['id', 'entity']);
        if ($records->isEmpty()) {
            $this->command?->warn('Tidak ada master_records. Jalankan MasterDataSeeder dulu.');
            return;
        }

        $recordsByEntity = [];
        foreach ($records as $rec) {
            $e = $normalizeEntity((string)$rec->entity);
            $recordsByEntity[$e][] = $rec->id;
        }

        // ===== 5) Build & upsert permissions (chunked) =====
        $now    = now();
        $buffer = [];

        $flush = function () use (&$buffer) {
            if (empty($buffer)) return;
            DB::table('master_record_permissions')->upsert(
                $buffer,
                ['master_record_id','user_id'],
                ['can_view','can_download','can_update','can_delete','updated_at']
            );
            $buffer = [];
        };

        foreach ($users as $u) {
            $roleKey = $normalizeRole($u->role_raw ?? '');
            if ($roleKey === '' || !isset($matrix[$roleKey])) {
                continue;
            }

            $rules   = $matrix[$roleKey];
            $toGrant = [];

            // a) aturan spesifik entity
            foreach ($rules as $entityName => $perm) {
                if ($entityName === '*') continue;
                $en = $normalizeEntity($entityName);
                if (!empty($recordsByEntity[$en])) {
                    foreach ($recordsByEntity[$en] as $rid) {
                        $toGrant[$rid] = $perm;
                    }
                }
            }

            // b) wildcard *
            if (isset($rules['*'])) {
                $perm = $rules['*'];
                foreach ($records as $rec) {
                    if (!isset($toGrant[$rec->id])) {
                        $toGrant[$rec->id] = $perm;
                    }
                }
            }

            // c) buffer write
            foreach ($toGrant as $rid => $perm) {
                $buffer[] = [
                    'id'               => (string) Str::uuid(),
                    'master_record_id' => $rid,
                    'user_id'          => $u->uid,
                    'can_view'         => (bool) ($perm['can_view']     ?? false),
                    'can_download'     => (bool) ($perm['can_download'] ?? false),
                    'can_update'       => (bool) ($perm['can_update']   ?? false),
                    'can_delete'       => (bool) ($perm['can_delete']   ?? false),
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
                if (count($buffer) >= 1000) {
                    $flush();
                }
            }
        }

        $flush();

        $this->command?->info('Master record access seeded (normalized roles/entities, idempotent, chunked).');
    }
}
