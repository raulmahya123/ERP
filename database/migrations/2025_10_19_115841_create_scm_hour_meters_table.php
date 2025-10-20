<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('scm_hour_meters', function (Blueprint $t) {
      $t->uuid('id')->primary();

      $t->foreignUuid('site_id')->constrained('sites');
      $t->date('date');
      $t->foreignUuid('shift_id')->constrained('shifts');
      $t->foreignUuid('unit_id')->constrained('assets');

      $t->decimal('hm_start', 10, 1)->default(0);
      $t->decimal('hm_end', 10, 1)->default(0);
      $t->decimal('hm_delta', 10, 1)->default(0);
      $t->boolean('anomaly')->default(false);

      $t->string('client_uid', 64)->nullable();
      $t->foreignUuid('created_by')->nullable()->constrained('users');

      $t->timestamps();

      $t->index(['site_id','date','shift_id']);
      $t->unique(['site_id','client_uid']);
    });
  }

  public function down(): void {
    Schema::dropIfExists('scm_hour_meters');
  }
};
