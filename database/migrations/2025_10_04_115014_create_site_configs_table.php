<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('site_configs', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('site_id');
            $t->uuid('commodity_id');
            $t->json('params')->nullable(); // { "hba":..., "ni_grade_min":..., "assay_method":..., "shift_roster":[...] }
            $t->timestamps();

            $t->unique(['site_id','commodity_id']);
            $t->foreign('site_id')->references('id')->on('sites')->cascadeOnDelete();
            $t->foreign('commodity_id')->references('id')->on('commodities')->cascadeOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('site_configs');
    }
};
