<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $t) {
            $t->uuid('id')->primary();

            // Simpan site_id tanpa FK (simple)
            $t->uuid('site_id')->index();

            $t->string('name');

            // Koordinat
            $t->decimal('longitude', 10, 7);
            $t->decimal('latitude',  10, 7);

            // Geofence default 100 m
            $t->unsignedSmallInteger('geofence_radius_m')->default(100);

            // Audit (tanpa FK biar simple & hindari dependensi)
            $t->uuid('created_by')->nullable()->index();
            $t->uuid('updated_by')->nullable()->index();

            $t->timestamps();
            $t->softDeletes(); // deleted_at

            // Unik per site untuk name
            $t->unique(['site_id','name'], 'uniq_locations_site_name');

            // Index bantu pencarian berdasar koordinat
            $t->index(['latitude','longitude'], 'idx_locations_latlng');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
