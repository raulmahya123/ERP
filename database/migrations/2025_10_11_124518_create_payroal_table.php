<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroal', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('user_id')->unique()->index();   // 1:1 dengan users
            $t->timestamps();

            // ========== Identitas dasar ==========
            $t->string('photo')->nullable();
            $t->string('employee_code', 64)->nullable()->unique();
            $t->string('full_name', 200)->nullable(); // boleh isi nama lengkap versi HR

            // ========== Identitas HR / Kependudukan ==========
            $t->string('nik', 32)->nullable()->unique();
            $t->string('npwp', 32)->nullable();
            $t->string('bpjs_ketenagakerjaan', 32)->nullable();
            $t->string('bpjs_kesehatan', 32)->nullable();

            // ========== Data Pribadi ==========
            $t->enum('gender', ['M','F'])->nullable()->comment('M=Male, F=Female');
            $t->string('marital_status', 20)->nullable()->comment('single/married/divorced/widowed');
            $t->string('birth_place', 100)->nullable();
            $t->date('birth_date')->nullable();
            $t->string('religion', 30)->nullable();
            $t->string('phone', 30)->nullable();

            // Alamat KTP
            $t->string('address_ktp_line1', 255)->nullable();
            $t->string('address_ktp_line2', 255)->nullable();
            $t->string('address_ktp_city', 100)->nullable();
            $t->string('address_ktp_province', 100)->nullable();
            $t->string('address_ktp_postal', 10)->nullable();

            // Alamat Domisili
            $t->string('address_dom_line1', 255)->nullable();
            $t->string('address_dom_line2', 255)->nullable();
            $t->string('address_dom_city', 100)->nullable();
            $t->string('address_dom_province', 100)->nullable();
            $t->string('address_dom_postal', 10)->nullable();

            // Kontak darurat
            $t->string('emergency_name', 100)->nullable();
            $t->string('emergency_relation', 50)->nullable();
            $t->string('emergency_phone', 30)->nullable();

            // ========== Bank & Pajak ==========
            $t->string('bank_name', 60)->nullable();
            $t->string('bank_branch', 100)->nullable();
            $t->string('bank_account_no', 60)->nullable();
            $t->string('bank_account_name', 120)->nullable();
            $t->string('tax_method', 20)->nullable()->comment('gross/gross_up/net');
            $t->string('ptkp_code', 10)->nullable()->comment('TK/0, K/0, K/1, dst.');

            // ========== Kepegawaian ==========
            $t->date('hire_date')->nullable();
            $t->date('resign_date')->nullable();
            $t->string('employment_status', 20)->nullable()->comment('probation/contract/permanent/intern');
            $t->string('job_title', 120)->nullable();
            $t->string('grade', 50)->nullable();
            $t->string('level', 50)->nullable();
            $t->string('department', 120)->nullable();
            $t->string('division', 120)->nullable();
            $t->uuid('site_id')->nullable()->index(); // site penempatan (HR)
            $t->string('shift_group', 10)->nullable();

            // ========== Gaji & Tunjangan ==========
            $t->decimal('base_salary', 15, 2)->nullable();
            $t->decimal('allowance_meal', 15, 2)->nullable();
            $t->decimal('allowance_transport', 15, 2)->nullable();
            $t->decimal('allowance_position', 15, 2)->nullable();
            $t->decimal('allowance_other', 15, 2)->nullable();
            $t->boolean('overtime_eligible')->default(true);

            // ========== Siklus Payroll ==========
            $t->string('payroll_cycle', 20)->nullable()->comment('monthly/biweekly/weekly');
            $t->string('currency', 8)->nullable()->default('IDR');

            // ========== Status HR ==========
            $t->timestamp('hired_at')->nullable();

            // ========== Payload fleksibel ==========
            $t->json('meta')->nullable();

            // ========== Self-service Lock ==========
            $t->boolean('self_locked')->default(false);
            $t->timestamp('self_locked_at')->nullable();
        });

        // FK (opsional – aktifkan jika tabel sites & users sudah ada stabil)
        Schema::table('payroal', function (Blueprint $t) {
            $t->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            // $t->foreign('site_id')->references('id')->on('sites')->nullOnDelete();
        });

        /**
         * BACKFILL: copy data dari kolom users (jika ada) ke payroal.
         * Aman dijalankan meski sebagian kolom tidak ada.
         */
        try {
            $usersCols = Schema::getColumnListing('users');

            $map = [
                'photo','employee_code','nik','npwp','bpjs_ketenagakerjaan','bpjs_kesehatan',
                'gender','marital_status','birth_place','birth_date','religion','phone',
                'address_ktp_line1','address_ktp_line2','address_ktp_city','address_ktp_province','address_ktp_postal',
                'address_dom_line1','address_dom_line2','address_dom_city','address_dom_province','address_dom_postal',
                'emergency_name','emergency_relation','emergency_phone',
                'bank_name','bank_branch','bank_account_no','bank_account_name','tax_method','ptkp_code',
                'hire_date','resign_date','employment_status','job_title','grade','level','department','division',
                'site_id','shift_group',
                'base_salary','allowance_meal','allowance_transport','allowance_position','allowance_other',
                'overtime_eligible','payroll_cycle','currency','hired_at','meta',
            ];

            // Ambil semua users (pilih kolom yang memang ada di tabel users)
            $selectCols = array_values(array_unique(array_merge(['id','name'], array_intersect($map, $usersCols))));
            $users = DB::table('users')->select($selectCols)->get();

            foreach ($users as $u) {
                // skip kalau sudah ada payroal
                $exists = DB::table('payroal')->where('user_id', $u->id)->exists();
                if ($exists) continue;

                $row = [
                    'id'         => (string) \Illuminate\Support\Str::uuid(),
                    'user_id'    => $u->id,
                    'full_name'  => $u->name ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                foreach ($map as $col) {
                    if (in_array($col, $usersCols, true)) {
                        $row[$col] = $u->{$col} ?? null;
                    }
                }

                DB::table('payroal')->insert($row);
            }
        } catch (\Throwable $e) {
            // Biarkan silent supaya migrasi tetap jalan di env yang belum lengkap
        }
    }

    public function down(): void
    {
        // Hapus FK dulu kalau diperlukan
        if (Schema::hasTable('payroal')) {
            Schema::table('payroal', function (Blueprint $t) {
                try { $t->dropForeign(['user_id']); } catch (\Throwable $e) {}
                try { $t->dropForeign(['site_id']); } catch (\Throwable $e) {}
            });
        }
        Schema::dropIfExists('payroal');
    }
};
