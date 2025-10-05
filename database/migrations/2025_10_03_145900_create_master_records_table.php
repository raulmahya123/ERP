<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('master_records', function (Blueprint $t) {
            // Pastikan engine mendukung FK
            $t->engine = 'InnoDB';

            $t->uuid('id')->primary();

            // FK ke master_entities (nullable, set null saat entity dihapus)
            $t->uuid('master_entity_id')->nullable();

            // Denormalisasi key entity untuk filter cepat
            $t->string('entity', 64);      // contoh: pits, stockpiles, units
            $t->string('name', 191);
            $t->string('code', 128)->nullable();

            // Kolom deskripsi (dibutuhkan oleh seeder)
            $t->text('description')->nullable();

            // Extra info (JSON — fleksibel)
            $t->json('extra')->nullable();

            // Scope site (nullable = global)
            $t->uuid('site_id')->nullable();

            // Pembuat (nullable)
            $t->uuid('created_by')->nullable();

            $t->timestamps();

            // ===== Index bantu =====
            $t->index(['entity', 'name'],       'idx_master_records_entity_name');
            $t->index(['entity', 'site_id'],    'idx_master_records_entity_site');
            $t->index(['site_id', 'created_by'],'idx_master_records_site_creator');
            $t->index('master_entity_id',       'idx_master_records_master_entity');

            // ===== Unique kombinasi =====
            // Catatan: MySQL mengizinkan multiple NULL di kolom unique -> aman untuk code=NULL.
            $t->unique(['entity', 'site_id', 'code'], 'uniq_master_records_entity_site_code');

            // ===== Definisikan FK dengan nama eksplisit =====
            $t->foreign('master_entity_id', 'fk_mrecords_master_entity')
              ->references('id')->on('master_entities')
              ->nullOnDelete();

            $t->foreign('site_id', 'fk_mrecords_site')
              ->references('id')->on('sites')
              ->nullOnDelete();

            $t->foreign('created_by', 'fk_mrecords_created_by')
              ->references('id')->on('users')
              ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_records');
    }
};
