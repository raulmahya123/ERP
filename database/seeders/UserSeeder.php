<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use App\Models\Division;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        $accounts = [
            ['name'=>'Admin GM',      'email'=>'admin@local.test',   'password'=>'password123','role_key'=>'gm',          'division_key'=>'plant'],
            ['name'=>'Manager Ops',   'email'=>'manager@local.test', 'password'=>'password123','role_key'=>'manager',     'division_key'=>'plant'],
            ['name'=>'Foreman Site',  'email'=>'foreman@local.test', 'password'=>'password123','role_key'=>'foreman',     'division_key'=>'plant'],
            ['name'=>'Operator Unit', 'email'=>'operator@local.test','password'=>'password123','role_key'=>'operator',    'division_key'=>'plant'],
            ['name'=>'HSE Officer',   'email'=>'hse@local.test',     'password'=>'password123','role_key'=>'hse_officer', 'division_key'=>'hse'],
            ['name'=>'HR Staff',      'email'=>'hr@local.test',      'password'=>'password123','role_key'=>'hr',          'division_key'=>'hr'],
            ['name'=>'Finance Staff', 'email'=>'finance@local.test', 'password'=>'password123','role_key'=>'finance',     'division_key'=>'finance'],
        ];

        foreach ($accounts as $a) {
            $role     = Role::where('key', $a['role_key'])->first();
            $division = !empty($a['division_key']) ? Division::where('key', $a['division_key'])->first() : null;

            // Ini akan update role_id & division_id jika user sudah ada
            User::updateOrCreate(
                ['email' => $a['email']],
                [
                    'name'        => $a['name'],
                    // CATATAN: ini akan reset password setiap kali seeding.
                    // Kalau tidak mau reset password existing, lihat alternatif di bawah.
                    'password'    => Hash::make($a['password']),
                    'role_id'     => $role?->id,
                    'division_id' => $division?->id,
                ]
            );
        }
    }
}
