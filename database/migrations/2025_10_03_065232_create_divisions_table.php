<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // === Buat tabel divisions ===
        Schema::create('divisions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key')->unique();              // contoh: plant, finance
            $table->string('name');                       // nama lengkap divisi
            $table->text('description')->nullable();      // deskripsi opsional
            $table->timestamps();
        });

        // === Tambahkan division_id ke tabel users ===
        Schema::table('users', function (Blueprint $table) {
            // pastikan users.id juga UUID supaya tipe konsisten
            $table->foreignUuid('division_id')
                  ->nullable()
                  ->after('role_id') // taruh setelah role_id biar rapi
                  ->constrained('divisions')
                  ->nullOnDelete(); // kalau division dihapus, set null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // drop kolom & FK dulu dari users
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users','division_id')) {
                $table->dropForeign(['division_id']);
                $table->dropColumn('division_id');
            }
        });

        // drop tabel divisions
        Schema::dropIfExists('divisions');
    }
};
