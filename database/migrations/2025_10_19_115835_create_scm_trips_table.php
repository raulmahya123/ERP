<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('scm_trips', function (Blueprint $t) {
      $t->uuid('id')->primary();

      $t->foreignUuid('site_id')->constrained('sites')->cascadeOnUpdate()->restrictOnDelete();
      $t->date('date');
      $t->foreignUuid('shift_id')->constrained('shifts');
      // UNIT/ALAT: refer ke assets (bukan units)
      $t->foreignUuid('unit_id')->constrained('assets');

      $t->foreignUuid('operator_id')->nullable()->constrained('users');

      // PIT & STOCKPILE: refer ke locations
      $t->foreignUuid('pit_id')->nullable()->constrained('locations');
      $t->foreignUuid('from_stockpile_id')->nullable()->constrained('locations');
      $t->foreignUuid('to_stockpile_id')->nullable()->constrained('locations');

      $t->foreignUuid('commodity_id')->constrained('commodities');

      $t->string('material_type', 30)->nullable();
      $t->decimal('tonnage', 10, 2)->default(0);
      $t->decimal('distance_km', 8, 2)->nullable();
      $t->dateTime('start_time')->nullable();
      $t->dateTime('end_time')->nullable();

      $t->enum('status', ['draft','submitted','validated','approved'])->default('draft')->index();

      // WB tickets: untuk sekarang TANPA FK (hindari urutan migrasi)
      $t->uuid('wb_ticket_in_id')->nullable()->index();
      $t->uuid('wb_ticket_out_id')->nullable()->index();

      $t->string('client_uid', 64)->nullable();
      $t->text('notes')->nullable();

      $t->foreignUuid('created_by')->nullable()->constrained('users');

      $t->timestamps();
      $t->softDeletes();

      $t->index(['site_id','date','shift_id']);
      $t->unique(['site_id','client_uid']);
    });
  }

  public function down(): void {
    Schema::dropIfExists('scm_trips');
  }
};
