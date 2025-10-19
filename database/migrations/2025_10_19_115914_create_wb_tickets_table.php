<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void {
    Schema::create('wb_tickets', function (Blueprint $t) {
      $t->uuid('id')->primary();

      $t->foreignUuid('site_id')->constrained('sites');
      $t->string('ticket_no');
      $t->enum('direction', ['in','out']);
      $t->dateTime('ticket_time');

      // map relasi
      $t->foreignUuid('unit_id')->nullable()->constrained('assets');
      $t->foreignUuid('pit_id')->nullable()->constrained('locations');
      $t->foreignUuid('stockpile_id')->nullable()->constrained('locations');
      $t->foreignUuid('commodity_id')->nullable()->constrained('commodities');

      $t->decimal('gross', 10, 2)->default(0);
      $t->decimal('tare', 10, 2)->default(0);
      $t->decimal('net', 10, 2)->default(0);

      // pasangan IN/OUT: biarkan uuid plain agar tidak tergantung urutan FK
      $t->uuid('pair_id')->nullable()->index();
      $t->text('notes')->nullable();

      $t->timestamps();

      $t->unique(['site_id','ticket_no']);
      $t->index(['site_id','ticket_time']);
    });
  }

  public function down(): void {
    Schema::dropIfExists('wb_tickets');
  }
};
