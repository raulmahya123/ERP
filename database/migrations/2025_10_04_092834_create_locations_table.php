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

            // Sederhana: simpan site_id tanpa FK dulu biar gak ribet dependensi
            $t->uuid('site_id')->index();

            $t->string('name');

            // Koordinat (ikuti urutan yang kamu pakai)
            $t->decimal('longitude', 10, 7); // contoh: 106.8451300
            $t->decimal('latitude',  10, 7); // contoh:  -6.2146200

            // Geofence default 100 m
            $t->unsignedSmallInteger('geofence_radius_m')->default(100);

            $t->timestamps();

            // Unik per site biar gak dobel nama lokasi di site yang sama
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
