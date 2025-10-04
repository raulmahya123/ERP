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
        Schema::create('locations', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->string('name');
            $t->decimal('longitude', 10, 7); // contoh: 106.8451300
            $t->decimal('latitude', 10, 7);  // contoh:  -6.2146200
            $t->unsignedSmallInteger('years_of_collab')->nullable(); // berapa tahun kerja sama
            $t->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();

            $t->unique(['name', 'latitude', 'longitude']); // anti-duplikat
            $t->index(['latitude', 'longitude']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
