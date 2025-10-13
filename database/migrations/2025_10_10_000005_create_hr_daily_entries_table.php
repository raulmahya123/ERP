<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('hr_daily_entries', function (Blueprint $t) {
            $t->uuid('id')->primary();

            $t->uuid('site_id')->index();
            $t->uuid('user_id')->index();
            $t->date('date')->index();

            $t->enum('type', ['leave','permit','sick','shift_change','ga','mcu'])->index();
            $t->string('code', 20)->nullable();
            $t->text('reason')->nullable();

            $t->uuid('from_shift_id')->nullable()->index();
            $t->uuid('to_shift_id')->nullable()->index();

            $t->enum('status', ['pending','approved','rejected'])->default('pending')->index();
            $t->uuid('approved_by')->nullable()->index();
            $t->timestamp('approved_at')->nullable();

            $t->uuid('created_by')->nullable();
            $t->uuid('updated_by')->nullable();

            $t->json('meta')->nullable();

            $t->timestamps();
            $t->softDeletes();

            $t->unique(['site_id','user_id','date','type','code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_daily_entries');
    }
};
