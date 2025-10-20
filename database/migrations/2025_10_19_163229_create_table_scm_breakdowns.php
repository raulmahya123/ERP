<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('scm_breakdowns', function (Blueprint $t) {
      $t->uuid('id')->primary();

      $t->foreignUuid('site_id')->constrained('sites');
      $t->foreignUuid('unit_id')->constrained('assets');

      // planned: maintenance terjadwal, unplanned: rusak/mendadak
      $t->enum('category', ['planned','unplanned', 'standby'])->default('unplanned');

      // opsional: kode sebab (free text/codebook)
      $t->string('cause_code', 64)->nullable();

      $t->dateTime('start_at');
      $t->dateTime('end_at')->nullable(); // boleh kosong saat masih berlangsung
      $t->decimal('duration_hours', 10, 2)->default(0); // dihitung otomatis

      $t->text('notes')->nullable();

      $t->string('client_uid', 64)->nullable();
      $t->foreignUuid('created_by')->nullable()->constrained('users');

      $t->timestamps();

      $t->index(['site_id','start_at']);
      $t->index(['unit_id','start_at']);
      $t->unique(['site_id','client_uid']); // buat sinkronisasi offline
    });
  }

  public function down(): void {
    Schema::dropIfExists('scm_breakdowns');
  }
};
