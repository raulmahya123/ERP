<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            ['key'=>'gm','name'=>'General Manager','description'=>'Full access'],
            ['key'=>'manager','name'=>'Manager','description'=>'Manager level access'],
            ['key'=>'foreman','name'=>'Foreman','description'=>'Site foreman'],
            ['key'=>'operator','name'=>'Operator','description'=>'Machine operator'],
            ['key'=>'hse_officer','name'=>'HSE Officer','description'=>'Safety & environment'],
            ['key'=>'hr','name'=>'HR','description'=>'Human resources'],
            ['key'=>'finance','name'=>'Finance','description'=>'Finance team'],
        ];

        foreach ($roles as $r) {
            Role::firstOrCreate(['key'=>$r['key']], $r);
        }
    }
}
