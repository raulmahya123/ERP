<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $t) {
            if (!Schema::hasColumn('users', 'default_site_id')) {
                $t->uuid('default_site_id')->nullable()->after('remember_token');
                $t->foreign('default_site_id')->references('id')->on('sites')->nullOnDelete();
            }
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $t) {
            if (Schema::hasColumn('users', 'default_site_id')) {
                $t->dropForeign(['default_site_id']);
                $t->dropColumn('default_site_id');
            }
        });
    }
};
