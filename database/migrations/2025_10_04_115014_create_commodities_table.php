<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('commodities', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('code', 10)->unique();   // contoh: "NI", "COAL", "AU"
            $t->string('name', 100);
            $t->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('commodities');
    }
};
