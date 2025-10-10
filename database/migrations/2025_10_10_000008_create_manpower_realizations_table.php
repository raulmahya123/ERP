<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('manpower_realizations', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('site_id')->index();
            $t->date('date')->index();
            $t->enum('shift_slot',['A','B','C','D','NON'])->index();
            $t->string('department',50)->index();
            $t->unsignedSmallInteger('actual_headcount')->default(0);
            $t->unsignedSmallInteger('actual_operators')->default(0);
            $t->unsignedSmallInteger('actual_mechanics')->default(0);
            $t->unsignedSmallInteger('actual_helpers')->default(0);
            $t->unsignedSmallInteger('actual_others')->default(0);
            $t->decimal('production_tonnage',10,2)->default(0); // untuk rasio produktivitas
            $t->decimal('manhours',10,2)->default(0);
            $t->json('meta')->nullable();
            $t->timestamps();
            $t->unique(['site_id','date','shift_slot','department']);
        });
    }
    public function down(): void { Schema::dropIfExists('manpower_realizations'); }
};
