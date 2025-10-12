<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('attendances', function (Blueprint $t) {
            $t->uuid('id')->primary();

            // Context
            $t->foreignUuid('site_id')->constrained('sites')->cascadeOnDelete();
            $t->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $t->date('work_date')->index();
            $t->foreignUuid('shift_id')->nullable()->constrained('shifts')->nullOnDelete();

            // Sumber input
            $t->enum('source', ['manual','fingerprint','mobile_gps'])->index();

            // Waktu absen
            $t->dateTime('check_in_at')->nullable();
            $t->dateTime('check_out_at')->nullable();

            // Lokasi referensi (FK ke locations)
            $t->foreignUuid('location_in_id')->nullable()->constrained('locations')->nullOnDelete();
            $t->foreignUuid('location_out_id')->nullable()->constrained('locations')->nullOnDelete();

            // Koordinat aktual user
            $t->decimal('gps_in_lat', 10, 7)->nullable();
            $t->decimal('gps_in_lng', 10, 7)->nullable();
            $t->decimal('gps_out_lat', 10, 7)->nullable();
            $t->decimal('gps_out_lng', 10, 7)->nullable();

            // Jarak & geofence (100m default di tabel locations)
            $t->unsignedInteger('distance_in_m')->nullable();
            $t->unsignedInteger('distance_out_m')->nullable();
            $t->boolean('outside_geofence_in')->default(false);
            $t->boolean('outside_geofence_out')->default(false);

            // Perangkat
            $t->string('device_id')->nullable();

            // Durasi & performa (tanpa overtime_minutes)
            $t->unsignedSmallInteger('late_minutes')->default(0);
            $t->unsignedSmallInteger('early_leave_minutes')->default(0);
            $t->unsignedSmallInteger('work_minutes')->default(0);

            // Status hari itu
            $t->enum('status', ['present','absent','leave','permit','sick','off','unknown'])
              ->default('unknown')->index();

            // Flag tambahan
            $t->json('flags')->nullable();

            $t->timestamps();

            // Unik: 1 user / site / tanggal
            $t->unique(['site_id','user_id','work_date'], 'uniq_attendances_site_user_date');
        });
    }

    public function down(): void {
        Schema::dropIfExists('attendances');
    }
};
