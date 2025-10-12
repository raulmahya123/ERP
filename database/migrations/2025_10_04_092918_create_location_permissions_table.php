<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::create('location_permissions', function (Blueprint $t) {
            $t->uuid('id')->primary();

            // === 3 relasi: site, lokasi, user ===
            $t->foreignUuid('site_id')->constrained('sites')->cascadeOnDelete();
            $t->foreignUuid('location_id')->constrained('locations')->cascadeOnDelete();
            $t->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();

            // Hak akses granular
            $t->boolean('can_view')->default(true);
            $t->boolean('can_update')->default(false);
            $t->boolean('can_delete')->default(false);

            $t->timestamps();

            // Unik per (site, lokasi, user)
            $t->unique(['site_id','location_id','user_id'], 'uniq_site_location_user');

            // Index bantu query
            $t->index(['site_id','user_id'], 'idx_lp_site_user');
        });
    }

    public function down(): void {
        Schema::dropIfExists('location_permissions');
    }
};
