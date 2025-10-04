<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MasterRecordAccessSeeder extends Seeder
{
    public function run(): void
    {
        // Safety: pastikan tabel ada
        $schema = DB::getSchemaBuilder();
        if (!$schema->hasTable('master_records') || !$schema->hasTable('master_record_permissions') || !$schema->hasTable('users') || !$schema->hasTable('roles')) {
            $this->command?->warn('Tables not found. Make sure users, roles, master_records, master_record_permissions exist.');
            return;
        }

        // ====== Role → Entity → Permission Matrix ======
        // true = grant, false/absent = no
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

        // Ambil users beserta role.key
        $users = DB::table('users')
            ->leftJoin('roles', 'roles.id', '=', 'users.role_id')
            ->get(['users.id as uid', 'roles.key as role_key']);

        if ($users->isEmpty()) {
            $this->command?->warn('No users found. Seed users & roles first.');
            return;
        }

        // Ambil semua master_records (id, entity)
        $records = DB::table('master_records')->get(['id','entity']);
        if ($records->isEmpty()) {
            $this->command?->warn('No master_records found. Seed MasterRecordSeeder first.');
            return;
        }

        // Build payload berdasarkan matrix
        $now = now();
        $payload = [];

        foreach ($users as $u) {
            $roleKey = $u->role_key ?? ''; // bisa null kalau belum punya role
            if (!isset($matrix[$roleKey])) {
                continue; // skip user tanpa aturan
            }
            $rules = $matrix[$roleKey];

            foreach ($records as $rec) {
                // Tentukan permission rule untuk record ini
                $perm = null;

                // 1) coba rule spesifik entity
                if (isset($rules[$rec->entity])) {
                    $perm = $rules[$rec->entity];
                }
                // 2) fallback wildcard '*'
                elseif (isset($rules['*'])) {
                    $perm = $rules['*'];
                }

                if (!$perm) {
                    continue;
                }

                $payload[] = [
                    'id'               => (string) Str::uuid(),
                    'master_record_id' => $rec->id,
                    'user_id'          => $u->uid,
                    'can_view'         => (bool) ($perm['can_view']     ?? false),
                    'can_download'     => (bool) ($perm['can_download'] ?? false),
                    'can_update'       => (bool) ($perm['can_update']   ?? false),
                    'can_delete'       => (bool) ($perm['can_delete']   ?? false),
                    'created_at'       => $now,
                    'updated_at'       => $now,
                ];
            }
        }

        if (empty($payload)) {
            $this->command?->info('No permissions to insert based on current matrix/users/records.');
            return;
        }

        // Hindari duplikat: gunakan UPSERT by (master_record_id, user_id)
        // Catatan: pastikan ada unique index ['master_record_id','user_id'] di tabel.
        // Jika tidak, pakai insert or ignore / cek eksistensi manual.
        DB::table('master_record_permissions')->upsert(
            $payload,
            ['master_record_id','user_id'],
            ['can_view','can_download','can_update','can_delete','updated_at']
        );

        $this->command?->info('Master record access seeded.');
    }
}
