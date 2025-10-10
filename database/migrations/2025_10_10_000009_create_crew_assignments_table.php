<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('crew_assignments', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('site_id')->index();
            $t->date('date')->index();
            $t->enum('shift_slot',['A','B','C','D','NON'])->index();
            $t->uuid('user_id')->index();
            $t->uuid('equipment_id')->nullable()->index();
            $t->string('role',30)->index(); // driver, helper, mechanic, welder, operator
            $t->string('activity_code',50)->nullable(); // hauling, pumping, welding
            $t->string('remarks')->nullable();
            $t->timestamps();
            $t->unique(['site_id','date','shift_slot','user_id','equipment_id','role'],'crew_unique');
        });
    }
    public function down(): void { Schema::dropIfExists('crew_assignments'); }
};
