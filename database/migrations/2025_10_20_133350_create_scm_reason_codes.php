<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scm_reason_codes', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('site_id')->constrained('sites')->cascadeOnDelete();
            $t->string('code', 30);
            $t->string('name', 80);
            $t->enum('category', ['idle', 'standby', 'breakdown', 'no_load', 'quality', 'weather', 'queue', 'other']);
            $t->boolean('is_downtime')->default(true);
            $t->boolean('is_billable')->default(false);
            $t->boolean('active')->default(true);
            $t->json('extra')->nullable();
            $t->timestamps();

            $t->unique(['site_id', 'code']);
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('scm_reason_codes');
    }
};
