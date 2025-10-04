<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('master_records', function (Blueprint $t) {
            // pakai InnoDB biar FK jalan (opsional kalau default sudah InnoDB)
            $t->engine = 'InnoDB';

            $t->uuid('id')->primary();

            $t->string('entity');       // contoh: pits, stockpiles, equipments
            $t->string('name');
            $t->string('code')->nullable();
            $t->text('description')->nullable();
            $t->json('extra')->nullable();

            // pembuat (UUID ke users.id)
            $t->foreignUuid('created_by')->nullable()
              ->constrained('users')->nullOnDelete();

            $t->timestamps();

            // index bantu
            $t->index(['entity', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_records');
    }
};
