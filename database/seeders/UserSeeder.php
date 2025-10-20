<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Division;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name'=>'Admin GM',      'email'=>'imyharis@gmail.com',   'password'=>'password123','role_key'=>'gm',          'division_key'=>'plant'],
            ['name'=>'Manager Ops',   'email'=>'manager@local.test', 'password'=>'password123','role_key'=>'manager',     'division_key'=>'plant'],
            ['name'=>'Foreman Site',  'email'=>'foreman@local.test', 'password'=>'password123','role_key'=>'foreman',     'division_key'=>'plant'],
            ['name'=>'Operator Unit', 'email'=>'operator@local.test','password'=>'password123','role_key'=>'operator',    'division_key'=>'plant'],
            ['name'=>'HSE Officer',   'email'=>'hse@local.test',     'password'=>'password123','role_key'=>'hse_officer', 'division_key'=>'hse'],
            ['name'=>'HR Staff',      'email'=>'hr@local.test',      'password'=>'password123','role_key'=>'hr',          'division_key'=>'hr'],
            ['name'=>'Finance Staff', 'email'=>'finance@local.test', 'password'=>'password123','role_key'=>'finance',     'division_key'=>'finance'],
        ];

        // Safety check
        if (!Schema::hasTable('users')) {
            $this->command?->warn('Table users tidak ditemukan. Jalankan migrasi dulu.');
            return;
        }

        foreach ($accounts as $a) {
            // Cari role & division (aman walau belum ada)
            $role     = Role::where('key', $a['role_key'])->orWhere('name', $a['role_key'])->first();
            $division = !empty($a['division_key'])
                        ? Division::where('key', $a['division_key'])->orWhere('name', $a['division_key'])->first()
                        : null;

            // Jika role tidak ada, buat otomatis minimal stub
            if (!$role) {
                $role = Role::create([
                    'id'   => (string) Str::uuid(),
                    'key'  => $a['role_key'],
                    'name' => Str::title(str_replace('_', ' ', $a['role_key'])),
                ]);
                $this->command?->info("Role '{$a['role_key']}' dibuat otomatis.");
            }

            // Jika division tidak ada, buat stub juga
            if ($division === null && !empty($a['division_key']) && Schema::hasTable('divisions')) {
                $division = Division::create([
                    'id'   => (string) Str::uuid(),
                    'key'  => $a['division_key'],
                    'name' => Str::title(str_replace('_', ' ', $a['division_key'])),
                ]);
                $this->command?->info("Division '{$a['division_key']}' dibuat otomatis.");
            }

            // Update or create user
            $user = User::where('email', $a['email'])->first();

            if ($user) {
                // Hanya update role/division; tidak paksa reset password
                $user->update([
                    'name'        => $a['name'],
                    'role_id'     => $role->id,
                    'division_id' => $division?->id,
                ]);
            } else {
                // Buat user baru
                User::create([
                    'id'           => (string) Str::uuid(),
                    'name'         => $a['name'],
                    'email'        => $a['email'],
                    'password'     => Hash::make($a['password']),
                    'role_id'      => $role->id,
                    'division_id'  => $division?->id,
                ]);
            }
        }

        $this->command?->info('✅ UserSeeder selesai — akun default dibuat / diperbarui.');
    }
}
