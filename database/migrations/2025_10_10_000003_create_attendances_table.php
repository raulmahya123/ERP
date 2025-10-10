<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('attendances', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('site_id')->index();
            $t->uuid('user_id')->index();
            $t->date('work_date')->index();
            $t->uuid('shift_id')->nullable()->index();

            // sumber input: manual, fingerprint, mobile_gps
            $t->enum('source', ['manual','fingerprint','mobile_gps'])->index();

            $t->dateTime('check_in_at')->nullable();
            $t->dateTime('check_out_at')->nullable();

            $t->decimal('gps_in_lat',10,7)->nullable();
            $t->decimal('gps_in_lng',10,7)->nullable();
            $t->decimal('gps_out_lat',10,7)->nullable();
            $t->decimal('gps_out_lng',10,7)->nullable();
            $t->string('device_id')->nullable(); // id mesin atau device mobile
            $t->unsignedSmallInteger('late_minutes')->default(0);
            $t->unsignedSmallInteger('early_leave_minutes')->default(0);
            $t->unsignedSmallInteger('overtime_minutes')->default(0);
            $t->unsignedSmallInteger('work_minutes')->default(0);

            $t->enum('status', ['present','absent','leave','permit','sick','off','unknown'])->default('unknown')->index();
            $t->json('flags')->nullable(); // ["late","overtime_high","no_checkout","abnormal"]

            $t->timestamps();
            $t->unique(['site_id','user_id','work_date']);
        });
    }
    public function down(): void { Schema::dropIfExists('attendances'); }
};
