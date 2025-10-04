<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('location_permissions', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->foreignUuid('location_id')->constrained('locations')->cascadeOnDelete();
            $t->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();

            // hak akses granular
            $t->boolean('can_view')->default(true);
            $t->boolean('can_update')->default(false);
            $t->boolean('can_delete')->default(false);

            $t->timestamps();
            $t->unique(['location_id','user_id'], 'uniq_location_user');
        });
    }

    public function down(): void {
        Schema::dropIfExists('location_permissions');
    }
};