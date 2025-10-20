<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('scm_fuel_logs', function (Blueprint $t) {
      $t->uuid('id')->primary();

      $t->foreignUuid('site_id')->constrained('sites');
      $t->foreignUuid('unit_id')->constrained('assets');
      $t->foreignUuid('operator_id')->nullable()->constrained('users');

      $t->dateTime('dispensed_at');
      $t->enum('fuel_type', ['diesel','gasoline','other'])->default('diesel');
      $t->decimal('liter', 10, 2);
      $t->string('dispenser_id')->nullable();
      $t->string('receipt_no')->nullable();

      $t->string('client_uid', 64)->nullable();
      $t->foreignUuid('created_by')->nullable()->constrained('users');

      $t->timestamps();

      $t->index(['unit_id','dispensed_at']);
      $t->unique(['site_id','client_uid']);
    });
  }

  public function down(): void {
    Schema::dropIfExists('scm_fuel_logs');
  }
};
