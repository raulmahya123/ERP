<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('shift_rosters', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('site_id')->index();
            $t->uuid('user_id')->index();
            $t->date('roster_date')->index();
            $t->uuid('shift_id')->nullable()->index();
            $t->string('crew_code', 20)->nullable(); // misal Crew-1, Crew-2
            $t->string('remarks')->nullable();
            $t->timestamps();
            $t->unique(['site_id','user_id','roster_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('shift_rosters'); }
};
