<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pits', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('site_id')->constrained('sites')->cascadeOnDelete();

            $t->string('code', 40);
            $t->string('name', 120)->nullable();
            $t->boolean('active')->default(true);
            $t->json('extra')->nullable();
            $t->timestamps();

            $t->unique(['site_id','code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pits');
    }
};
