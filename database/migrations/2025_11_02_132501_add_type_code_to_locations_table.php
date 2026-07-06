<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('locations', function (Blueprint $t) {
            // kode opsional (unik per site). nullable agar data lama aman.
            $t->string('code', 30)->nullable()->after('site_id')->index();

            // tipe lokasi: pit / stockpile / dsb
            $t->string('type', 20)->nullable()->after('name')->index();

            // (opsional) unique per site + code (NULL boleh duplikat, aman)
            $t->unique(['site_id', 'code'], 'uniq_locations_site_code');
        });
    }

    public function down(): void
    {
        Schema::table('locations', function (Blueprint $t) {
            // hapus index unik kalau ada
            if (Schema::hasColumn('locations', 'code')) {
                $t->dropUnique('uniq_locations_site_code');
                $t->dropIndex(['code']); // aman walau sudah terhapus di atas
                $t->dropColumn('code');
            }
            if (Schema::hasColumn('locations', 'type')) {
                $t->dropIndex(['type']);
                $t->dropColumn('type');
            }
        });
    }
};
