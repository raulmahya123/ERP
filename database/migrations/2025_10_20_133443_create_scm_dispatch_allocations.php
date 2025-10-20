<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('scm_dispatch_allocations', function (Blueprint $t) {
      $t->uuid('id')->primary();
      $t->foreignUuid('site_id')->constrained('sites')->cascadeOnDelete();
      $t->date('work_date');
      $t->foreignUuid('shift_id')->constrained('shifts')->cascadeOnDelete();
      $t->foreignUuid('pit_id')->constrained('pits')->cascadeOnDelete();
      $t->foreignUuid('asset_id')->constrained('assets')->cascadeOnDelete();   // unit/alat
      $t->foreignUuid('operator_id')->constrained('users')->cascadeOnDelete();
      $t->uuid('route_id')->nullable(); // kalau ada tabel routes, nanti di-foreign-kan
      $t->time('planned_start')->nullable();
      $t->time('planned_end')->nullable();
      $t->enum('status', ['planned','in_progress','done','cancelled'])->default('planned');
      $t->text('notes')->nullable();
      $t->json('extra')->nullable();
      $t->timestamps();

      $t->index(['site_id','work_date','shift_id']);
    });
  }
  public function down(): void { Schema::dropIfExists('scm_dispatch_allocations'); }
};