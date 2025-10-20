<?php

// database/migrations/2025_10_20_000000_create_email_verification_codes_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('email_verification_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('user_id')->constrained()->cascadeOnDelete();
            $table->string('code', 6);
            $table->unsignedTinyInteger('attempts')->default(0); // hitung salah
            $table->timestamp('expires_at')->index();
            $table->timestamps();

            $table->index(['user_id','code']);
        });
    }
    public function down(): void { Schema::dropIfExists('email_verification_codes'); }
};
