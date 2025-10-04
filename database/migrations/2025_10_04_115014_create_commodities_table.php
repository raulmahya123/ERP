<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('commodities', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('code')->unique(); // coal, nickel, gold
            $t->string('name');
            $t->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('commodities');
    }
};
