<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Panggil seeder yang sudah dibuat
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            DivisionSeeder::class,
        ]);
    }
}
