<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('master_entities', function (Blueprint $t) {
            $t->engine = 'InnoDB';

            $t->uuid('id')->primary(); // -> char(36)
            $t->string('key', 64)->unique(); // mis. 'units','pits','stockpiles', dll
            $t->string('label', 191)->nullable();
            $t->boolean('enabled')->default(true);
            $t->unsignedInteger('sort')->default(0);
            $t->json('schema')->nullable();
            $t->string('icon', 64)->nullable();
            $t->string('color_from', 32)->nullable();
            $t->string('color_to', 32)->nullable();
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_entities');
    }
};
