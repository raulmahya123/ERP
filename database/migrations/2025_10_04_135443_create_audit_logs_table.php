<?php

// database/migrations/2025_10_04_000000_create_audit_logs_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable()->index(); // user yang melakukan
            $table->string('action');                     // nama aksi: login, create, update, delete
            $table->string('entity_type')->nullable();    // nama model/table: User, Course
            $table->uuid('entity_id')->nullable();        // id record yang terpengaruh
            $table->json('changes')->nullable();          // detail sebelum/sesudah
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('audit_logs');
    }
};
