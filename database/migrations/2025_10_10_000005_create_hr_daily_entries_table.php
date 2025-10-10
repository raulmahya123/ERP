<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('hr_daily_entries', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('site_id')->index();
            $t->uuid('user_id')->index();
            $t->date('date')->index();
            // cuti/izin/sakit/mutasi_shift
            $t->enum('type', ['leave','permit','sick','shift_change'])->index();
            $t->string('code', 20)->nullable();      // C1, I1, SK, dll
            $t->text('reason')->nullable();
            $t->uuid('from_shift_id')->nullable();
            $t->uuid('to_shift_id')->nullable();
            $t->json('meta')->nullable();
            $t->timestamps();
            $t->unique(['site_id','user_id','date','type','code']);
        });
    }
    public function down(): void { Schema::dropIfExists('hr_daily_entries'); }
};
