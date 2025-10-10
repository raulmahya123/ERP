<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('manpower_plans', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('site_id')->index();
            $t->date('date')->index();
            $t->enum('shift_slot',['A','B','C','D','NON'])->default('A')->index();
            $t->string('department',50)->index();
            $t->unsignedSmallInteger('planned_headcount')->default(0);
            $t->unsignedSmallInteger('planned_operators')->default(0);
            $t->unsignedSmallInteger('planned_mechanics')->default(0);
            $t->unsignedSmallInteger('planned_helpers')->default(0);
            $t->unsignedSmallInteger('planned_others')->default(0);
             $t->string('note',200)->nullable(); // ⬅️ kolom yang bikin error tadi
            $t->json('meta')->nullable();
            $t->timestamps();
            $t->unique(['site_id','date','shift_slot','department']);
        });
    }
    public function down(): void { Schema::dropIfExists('manpower_plans'); }
};
