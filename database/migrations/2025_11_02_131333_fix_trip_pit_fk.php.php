<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1) drop FK lama yang ke `locations`
        Schema::table('scm_trips', function (Blueprint $table) {
            // Nama default Laravel biasanya: scm_trips_pit_id_foreign
            $table->dropForeign('scm_trips_pit_id_foreign');
        });

        // 2) tambah FK baru ke `pits(id)`
        Schema::table('scm_trips', function (Blueprint $table) {
            // pastikan tipe kolom sudah uuid/char(36) dan nullable jika pit opsional
            // $table->uuid('pit_id')->nullable()->change(); // butuh doctrine/dbal jika perlu change
            $table->foreign('pit_id')
                ->references('id')->on('pits')
                ->nullOnDelete()     // kalau pit dihapus, set NULL
                ->cascadeOnUpdate(); // update id pit ikut
        });
    }

    public function down(): void
    {
        Schema::table('scm_trips', function (Blueprint $table) {
            $table->dropForeign('scm_trips_pit_id_foreign');
        });

        Schema::table('scm_trips', function (Blueprint $table) {
            $table->foreign('pit_id')
                ->references('id')->on('locations') // balik ke kondisi lama
                ->nullOnDelete()->cascadeOnUpdate();
        });
    }
};
