<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('sites', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('code')->unique();      // contoh: SUL-NI, KALSEL-COAL
            $t->string('name');                // contoh: Sulawesi - Nickel
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('sites');
    }
};
