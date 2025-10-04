<?php

// database/migrations/2025_10_04_000001_create_master_entities_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('master_entities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique();     // slug: vendors, materials, dll
            $table->string('label');             // nama tampil
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->json('schema')->nullable();  // opsional: field khusus per entity
            $table->string('icon')->nullable();  // opsional: svg/heroicon name
            $table->string('color_from')->nullable(); // opsional warna kartu
            $table->string('color_to')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('master_entities');
    }
};
