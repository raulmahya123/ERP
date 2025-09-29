<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique(); // e.g. gm, manager, foreman
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // add role_id to users (foreign uuid)
        Schema::table('users', function (Blueprint $table) {
            // Note: if users table already exists with id uuid, ensure types match
            $table->foreignUuid('role_id')->nullable()->after('id')->constrained('roles')->nullOnDelete();
        });
    }

    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
        Schema::dropIfExists('roles');
    }
};
