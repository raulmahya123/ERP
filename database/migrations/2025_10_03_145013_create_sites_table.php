<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sites', function (Blueprint $t) {
            // Pastikan kompatibel untuk FK
            $t->engine = 'InnoDB';
            $t->charset = 'utf8mb4';
            $t->collation = 'utf8mb4_unicode_ci';

            // PK UUID (char(36))
            $t->uuid('id')->primary();

            // Kode & nama site
            $t->string('code', 30)->unique();   // contoh: SUL-NI, KALSEL-COAL
            $t->string('name', 150);            // contoh: Sulawesi - Nickel

            $t->timestamps();

            // Index tambahan kalau perlu pencarian cepat
            $t->index('name');
        });
    }

    public function down(): void {
        Schema::dropIfExists('sites');
    }
};
