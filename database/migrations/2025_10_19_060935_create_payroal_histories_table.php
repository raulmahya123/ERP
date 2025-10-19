<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroal_histories', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('payroal_id')->index();
            $t->date('period')->index()->comment('Gunakan tgl 1 tiap bulan, contoh: 2025-09-01');
            $t->uuid('site_id')->nullable()->index();

            // Ringkasan angka
            $t->decimal('gross', 15, 2)->default(0);
            $t->decimal('deduction', 15, 2)->default(0);
            $t->decimal('net', 15, 2)->default(0);
            $t->decimal('take_home_pay', 15, 2)->default(0);

            // Payload fleksibel
            $t->json('earnings')->nullable();   // array komponen pendapatan
            $t->json('deductions')->nullable(); // array komponen potongan
            $t->json('meta')->nullable();       // catatan lain (jam lembur, shift, dsb.)

            // Siklus & pengiriman
            $t->string('status', 20)->default('draft')  // draft|locked|sent|void
              ->index();
            $t->timestamp('locked_at')->nullable();
            $t->timestamp('sent_at')->nullable();
            $t->string('emailed_to', 200)->nullable();
            $t->string('pdf_path', 255)->nullable();     // storage path kalau di-generate PDF
            $t->string('view_token', 64)->nullable()->unique(); // untuk signed link sederhana

            $t->timestamps();

            $t->unique(['payroal_id','period']); // 1 payslip per user per bulan
            $t->foreign('payroal_id')->references('id')->on('payroal')->cascadeOnDelete();
            // $t->foreign('site_id')->references('id')->on('sites')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroal_histories');
    }
};
