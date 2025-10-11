<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hr_daily_entries', function (Blueprint $t) {
            if (!Schema::hasColumn('hr_daily_entries','status')) {
                $t->enum('status',['pending','approved','rejected'])->default('pending')->index();
            }
            if (!Schema::hasColumn('hr_daily_entries','approved_by')) {
                $t->uuid('approved_by')->nullable()->index();
            }
            if (!Schema::hasColumn('hr_daily_entries','approved_at')) {
                $t->timestamp('approved_at')->nullable();
            }
            if (!Schema::hasColumn('hr_daily_entries','updated_by')) {
                $t->uuid('updated_by')->nullable()->index();
            }
            if (!Schema::hasColumn('hr_daily_entries','created_by')) {
                $t->uuid('created_by')->nullable()->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('hr_daily_entries', function (Blueprint $t) {
            if (Schema::hasColumn('hr_daily_entries','status'))      $t->dropColumn('status');
            if (Schema::hasColumn('hr_daily_entries','approved_by')) $t->dropColumn('approved_by');
            if (Schema::hasColumn('hr_daily_entries','approved_at')) $t->dropColumn('approved_at');
            if (Schema::hasColumn('hr_daily_entries','updated_by'))  $t->dropColumn('updated_by');
            if (Schema::hasColumn('hr_daily_entries','created_by'))  $t->dropColumn('created_by');
        });
    }
};
