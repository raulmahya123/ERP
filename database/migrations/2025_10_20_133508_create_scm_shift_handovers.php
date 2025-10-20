<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('scm_shift_handovers', function (Blueprint $t) {
      $t->uuid('id')->primary();
      $t->foreignUuid('site_id')->constrained('sites')->cascadeOnDelete();
      $t->date('handover_date');
      $t->foreignUuid('from_shift_id')->constrained('shifts')->cascadeOnDelete();
      $t->foreignUuid('to_shift_id')->constrained('shifts')->cascadeOnDelete();
      $t->enum('weather', ['clear','cloudy','rain','storm','other'])->nullable();
      $t->text('issues')->nullable();
      $t->text('targets')->nullable();
      $t->text('notes')->nullable();
      $t->json('extra')->nullable();
      $t->timestamps();

      $t->unique(
        ['site_id','handover_date','from_shift_id','to_shift_id'],
        'sho_site_date_from_to_uq'
      );
    });

    Schema::create('scm_shift_handover_items', function (Blueprint $t) {
      $t->uuid('id')->primary();
      $t->foreignUuid('handover_id')->constrained('scm_shift_handovers')->cascadeOnDelete();
      $t->foreignUuid('pit_id')->constrained('pits')->cascadeOnDelete();
      $t->text('notes')->nullable();
      $t->timestamps();

      $t->unique(['handover_id','pit_id'], 'sho_item_handover_pit_uq');
    });
  }

  public function down(): void {
    Schema::dropIfExists('scm_shift_handover_items');
    Schema::dropIfExists('scm_shift_handovers');
  }
};
