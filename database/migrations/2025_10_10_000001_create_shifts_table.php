<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('shifts', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('site_id')->nullable()->index();
            $t->string('code', 20)->index();       // A, B, C, D
            $t->string('name', 50);                // Day, Night, Non-Shift
            $t->time('start_at');
            $t->time('end_at');
            $t->unsignedSmallInteger('break_minutes')->default(0);
            $t->boolean('overnight')->default(false); // lintas hari
            $t->json('meta')->nullable();
            $t->timestamps();
            $t->unique(['site_id','code']);
        });
    }
    public function down(): void { Schema::dropIfExists('shifts'); }
};
