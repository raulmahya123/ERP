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

            // FK wajib match UUID ke tabel tujuan
            $t->foreignUuid('master_record_id')
              ->constrained('master_records')
              ->cascadeOnDelete();

            $t->foreignUuid('user_id')
              ->constrained('users')
              ->cascadeOnDelete();

            // Hak akses granular
            $t->boolean('can_view')->default(false);
            $t->boolean('can_download')->default(false);
            $t->boolean('can_update')->default(false);
            $t->boolean('can_delete')->default(false);

            $t->timestamps();

            // Tidak boleh duplikat izin untuk kombinasi (record, user)
            $t->unique(['master_record_id', 'user_id'], 'uniq_master_record_user');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_record_permissions');
    }
};
