<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('master_record_permissions', function (Blueprint $t) {
            $t->engine = 'InnoDB';

            $t->uuid('id')->primary();

            $t->uuid('master_record_id');
            $t->uuid('user_id');

            $t->boolean('can_view')->default(false);
            $t->boolean('can_download')->default(false);
            $t->boolean('can_update')->default(false);
            $t->boolean('can_delete')->default(false);

            $t->timestamps();

            // Unique kombinasi
            $t->unique(['master_record_id', 'user_id'], 'uniq_master_record_user');

            // Index bantu
            $t->index('user_id', 'idx_mrperm_user');
            $t->index('master_record_id', 'idx_mrperm_master_record');
            $t->index(['user_id', 'can_view'], 'idx_mrperm_user_view');
            $t->index(['master_record_id', 'can_view'], 'idx_mrperm_record_view');

            // FK dengan nama eksplisit
            $t->foreign('master_record_id', 'fk_mrperm_master_record')
              ->references('id')->on('master_records')
              ->cascadeOnDelete();

            $t->foreign('user_id', 'fk_mrperm_user')
              ->references('id')->on('users')
              ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_record_permissions');
    }
};
