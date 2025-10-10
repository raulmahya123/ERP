<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('timesheets', function (Blueprint $t) {
            $t->uuid('id')->primary();

            // Relations
            $t->uuid('site_id')->index();
            $t->uuid('user_id')->index();
            $t->uuid('shift_id')->nullable()->index();
            $t->uuid('equipment_id')->nullable()->index(); // refer to assets.id (equipment)

            // Core fields
            $t->date('work_date')->index();
            $t->string('activity_code', 50)->index(); // drilling, hauling, fueling, maintenance
            $t->string('activity_desc')->nullable();
            $t->decimal('hours', 4, 2)->default(0);          // jam kerja (00.00–99.99)
            $t->decimal('overtime_hours', 4, 2)->default(0); // lembur
            $t->string('cost_center')->nullable();
            $t->json('meta')->nullable();

            $t->timestamps();

            // Uniqueness per orang per hari per aktivitas per alat per site
            $t->unique(
                ['site_id','user_id','work_date','activity_code','equipment_id'],
                'uniq_timesheet'
            );

            // Foreign keys (adjust table names if different)
            $t->foreign('site_id')->references('id')->on('sites')->cascadeOnDelete();
            $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $t->foreign('shift_id')->references('id')->on('shifts')->nullOnDelete();
            $t->foreign('equipment_id')->references('id')->on('assets')->nullOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('timesheets');
    }
};
