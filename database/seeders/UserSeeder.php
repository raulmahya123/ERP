<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $accounts = [
            [
                'name' => 'Admin GM',
                'email' => 'admin@local.test',
                'password' => 'password123',
                'role_key' => 'gm',
            ],
            [
                'name' => 'Manager Ops',
                'email' => 'manager@local.test',
                'password' => 'password123',
                'role_key' => 'manager',
            ],
            [
                'name' => 'Foreman Site',
                'email' => 'foreman@local.test',
                'password' => 'password123',
                'role_key' => 'foreman',
            ],
            [
                'name' => 'Operator Unit',
                'email' => 'operator@local.test',
                'password' => 'password123',
                'role_key' => 'operator',
            ],
            [
                'name' => 'HSE Officer',
                'email' => 'hse@local.test',
                'password' => 'password123',
                'role_key' => 'hse_officer',
            ],
            [
                'name' => 'HR Staff',
                'email' => 'hr@local.test',
                'password' => 'password123',
                'role_key' => 'hr',
            ],
            [
                'name' => 'Finance Staff',
                'email' => 'finance@local.test',
                'password' => 'password123',
                'role_key' => 'finance',
            ],
        ];

        foreach ($accounts as $a) {
            $role = Role::where('key', $a['role_key'])->first();

            User::firstOrCreate(
                ['email' => $a['email']], // cari by email
                [
                    'name' => $a['name'],
                    'password' => Hash::make($a['password']),
                    'role_id' => $role?->id,
                ]
            );
        }
    }
}
