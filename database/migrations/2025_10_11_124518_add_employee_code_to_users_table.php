<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->string('employee_code', 64)
              ->nullable()
              ->after('email');
            $t->index('employee_code');
            // Kalau mau unik per sistem:
            // $t->unique('employee_code');
            // Atau unik per site (kalau multi-site):
            // $t->unique(['default_site_id','employee_code']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->dropIndex(['employee_code']); // nama index bisa berbeda di envmu
            // $t->dropUnique(['users_employee_code_unique']); // kalau pakai unique
            $t->dropColumn('employee_code');
        });
    }
};
