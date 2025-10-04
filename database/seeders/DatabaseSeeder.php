<?php

namespace Database\Seeders;

use App\Models\MasterRecord;
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
            DivisionSeeder::class,
            UserSeeder::class,
            MasterDataSeeder::class,
            MasterRecordAccessSeeder::class,
        ]);
    }
}
