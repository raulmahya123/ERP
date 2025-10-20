<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('scm_daily_plans', function (Blueprint $t) {
      $t->uuid('id')->primary();
      $t->foreignUuid('site_id')->constrained('sites')->cascadeOnDelete();
      $t->date('plan_date');
      $t->foreignUuid('shift_id')->constrained('shifts')->cascadeOnDelete();
      $t->text('remarks')->nullable();
      $t->json('extra')->nullable();
      $t->timestamps();

      $t->unique(['site_id','plan_date','shift_id']);
    });

  Schema::create('scm_daily_plan_items', function (Blueprint $t) {
    $t->uuid('id')->primary();
    $t->foreignUuid('daily_plan_id')->constrained('scm_daily_plans')->cascadeOnDelete();
    $t->foreignUuid('pit_id')->constrained('pits')->cascadeOnDelete(); // PENTING: match UUID
    $t->decimal('target_ton', 12, 2)->default(0);
    $t->unsignedInteger('target_ritase')->default(0);
    $t->text('notes')->nullable();
    $t->timestamps();

    $t->unique(['daily_plan_id','pit_id']);
});

  }
  public function down(): void {
    Schema::dropIfExists('scm_daily_plan_items');
    Schema::dropIfExists('scm_daily_plans');
  }
};