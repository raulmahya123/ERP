<?php

// database/migrations/2025_10_05_000010_create_assets_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('assets', function (Blueprint $t) {
            $t->uuid('id')->primary();

            $t->uuid('site_id'); // melekat ke site
            $t->foreign('site_id')->references('id')->on('sites')->cascadeOnDelete();

            // relasi ke master_records (kategori & cost center) — keduanya optional
            $t->uuid('asset_category_id')->nullable();
            $t->foreign('asset_category_id')->references('id')->on('master_records')->nullOnDelete();
            $t->uuid('cost_center_id')->nullable();
            $t->foreign('cost_center_id')->references('id')->on('master_records')->nullOnDelete();

            // identitas & spesifikasi umum
            $t->string('code',100)->nullable();    // unik per site
            $t->string('name',255);
            $t->string('brand',100)->nullable();
            $t->string('model',100)->nullable();
            $t->string('serial_no',150)->nullable();
            $t->string('plate_no',50)->nullable();     // nopol/unit no
            $t->string('engine_no',150)->nullable();
            $t->string('frame_no',150)->nullable();

            // status & tanggal
            $t->string('status',30)->default('active'); // active/inactive/repair/sold/etc
            $t->date('commissioned_at')->nullable();

            // lokasi/penanggung jawab opsional
            $t->string('location',150)->nullable();
            $t->uuid('assigned_to_user_id')->nullable()->index(); // jika mau

            // info finansial opsional
            $t->decimal('acq_cost',18,2)->nullable();
            $t->date('acq_date')->nullable();

            $t->json('extra')->nullable(); // fleksibel

            $t->uuid('created_by')->nullable()->index();
            $t->timestamps();

            $t->unique(['site_id','code']); // unik per site
            $t->index(['site_id','asset_category_id']);
            $t->index(['site_id','cost_center_id']);
            $t->index(['status','site_id']);
        });
    }
    public function down(): void {
        Schema::dropIfExists('assets');
    }
};
