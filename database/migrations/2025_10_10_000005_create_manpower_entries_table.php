<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('manpower_entries', function (Blueprint $t) {
            $t->uuid('id')->primary();

            // Context harian
            $t->uuid('site_id')->index();
            $t->date('date')->index();
            $t->enum('shift_slot', ['A','B','C','D','NON'])->index();

            // Tipe entri: PLAN (rencana), REAL (realisasi), ASSIGN (penugasan per user)
            $t->enum('entry_type', ['PLAN','REAL','ASSIGN'])->index();

            // Agregat utk PLAN/REAL (nullable untuk ASSIGN)
            $t->string('department', 50)->nullable()->index();
            // PLAN
            $t->unsignedSmallInteger('planned_headcount')->nullable();
            $t->unsignedSmallInteger('planned_operators')->nullable();
            $t->unsignedSmallInteger('planned_mechanics')->nullable();
            $t->unsignedSmallInteger('planned_helpers')->nullable();
            $t->unsignedSmallInteger('planned_others')->nullable();
            // REAL
            $t->unsignedSmallInteger('actual_headcount')->nullable();
            $t->unsignedSmallInteger('actual_operators')->nullable();
            $t->unsignedSmallInteger('actual_mechanics')->nullable();
            $t->unsignedSmallInteger('actual_helpers')->nullable();
            $t->unsignedSmallInteger('actual_others')->nullable();
            $t->decimal('production_tonnage', 10, 2)->nullable();
            $t->decimal('manhours', 10, 2)->nullable();

            // ASSIGN (nullable untuk PLAN/REAL)
            $t->uuid('user_id')->nullable()->index();
            $t->uuid('equipment_id')->nullable()->index();
            $t->string('role', 30)->nullable();
            $t->string('activity_code', 50)->nullable();
            $t->string('remarks')->nullable();

            // Umum
            $t->string('note', 200)->nullable();
            $t->json('meta')->nullable();

            $t->timestamps();

            // Unik gabungan (NULL tidak bentrok di MySQL)
            $t->unique(
                ['site_id','date','shift_slot','entry_type','department','user_id','equipment_id'],
                'uniq_mp_entries'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manpower_entries');
    }
};
