<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asset_assignments', function (Blueprint $t) {
            $t->engine = 'InnoDB'; // pastikan InnoDB utk FK

            $t->uuid('id')->primary();

            // Relasi utama (kolom + FK dalam satu langkah)
            $t->foreignUuid('asset_id')
              ->constrained('assets')
              ->cascadeOnDelete();

            // Asal & tujuan (nullable utk penempatan awal)
            $t->foreignUuid('from_site_id')
              ->nullable()
              ->constrained('sites')
              ->nullOnDelete();

            $t->foreignUuid('to_site_id')
              ->nullable()
              ->constrained('sites')
              ->nullOnDelete();

            // Opsional: pemegang (user) lama/baru
            $t->foreignUuid('from_user_id')
              ->nullable()
              ->constrained('users')
              ->nullOnDelete();

            $t->foreignUuid('to_user_id')
              ->nullable()
              ->constrained('users')
              ->nullOnDelete();

            // Audit
            $t->foreignUuid('created_by')
              ->nullable()
              ->constrained('users')
              ->nullOnDelete();

            // Metadata
            $t->date('assigned_at')->nullable();  // tanggal efektif mutasi/penempatan
            $t->text('note')->nullable();

            $t->timestamps();

            // Index yang sering dipakai
            $t->index(['asset_id', 'assigned_at']);
            $t->index('to_site_id');
            $t->index('from_site_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_assignments');
    }
};
