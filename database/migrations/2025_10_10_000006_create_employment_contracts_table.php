<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employment_contracts', function (Blueprint $t) {
            // ===== PRIMARY KEY & RELATION =====
            $t->uuid('id')->primary();
            $t->uuid('site_id')->nullable()->index();  // bisa null untuk kontrak HO
            $t->uuid('user_id')->index();               // relasi ke users.id

            // ===== DETAIL KONTRAK =====
            $t->enum('type', ['permanent', 'contract', 'outsourced'])->index()
              ->comment('Jenis hubungan kerja: tetap / kontrak / outsourcing');
            $t->string('vendor_name')->nullable()
              ->comment('Nama vendor jika tipe outsourced');

            $t->string('position', 100)->nullable()
              ->comment('Posisi atau jabatan dalam kontrak');
            $t->decimal('base_salary', 14, 2)->nullable()
              ->comment('Gaji pokok per bulan (optional, untuk payroll reference)');

            // ===== PERIODE KONTRAK =====
            $t->date('start_date')->comment('Tanggal mulai kontrak');
            $t->date('end_date')->nullable()->comment('Tanggal akhir kontrak (null jika tetap)');

            // ===== META =====
            $t->json('meta')->nullable()->comment('Informasi tambahan: remarks, file URL, dsb');

            // ===== TIMESTAMPS =====
            $t->timestamps();

            // ===== INDEX / CONSTRAINT =====
            $t->unique(['user_id', 'start_date'], 'uniq_user_startdate');
        });

        // Tambahkan foreign key di luar closure agar mudah dihapus pada rollback
        Schema::table('employment_contracts', function (Blueprint $t) {
            $t->foreign('user_id')
              ->references('id')->on('users')
              ->cascadeOnDelete();

            $t->foreign('site_id')
              ->references('id')->on('sites')
              ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employment_contracts', function (Blueprint $t) {
            $t->dropForeign(['user_id']);
            $t->dropForeign(['site_id']);
        });

        Schema::dropIfExists('employment_contracts');
    }
};
