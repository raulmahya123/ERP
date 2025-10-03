<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Division;

class DivisionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $divisions = [
            [
                'key' => 'hr',
                'name' => 'Human Resources',
                'description' => 'Mengelola SDM, rekrutmen, dan pengembangan karyawan'
            ],
            [
                'key' => 'finance',
                'name' => 'Finance',
                'description' => 'Mengelola akuntansi, budgeting, dan laporan keuangan'
            ],
            [
                'key' => 'she',
                'name' => 'Safety, Health & Environment',
                'description' => 'Keselamatan kerja, kesehatan, dan lingkungan'
            ],
            [
                'key' => 'scm',
                'name' => 'Supply Chain Management',
                'description' => 'Logistik, pengadaan, dan manajemen rantai pasok'
            ],
            [
                'key' => 'plant',
                'name' => 'Plant Department',
                'description' => 'Operasional pabrik dan pemeliharaan'
            ],
            [
                'key' => 'it',
                'name' => 'Information Technology',
                'description' => 'Sistem informasi, infrastruktur, dan digitalisasi'
            ],
        ];

        foreach ($divisions as $d) {
            Division::updateOrCreate(
                ['key' => $d['key']], // supaya tidak double kalau seed ulang
                $d
            );
        }
    }
}
