<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        // 1) Pastikan master_entities ada (kalau kamu sudah punya migration-nya, lewati bagian ini)
        if (!Schema::hasTable('master_entities')) {
            Schema::create('master_entities', function (Blueprint $table) {
                $table->uuid('id')->primary();
                $table->string('key')->unique();
                $table->string('label');
                $table->boolean('enabled')->default(true);
                $table->unsignedInteger('sort')->default(0);
                $table->json('schema')->nullable();
                $table->string('icon')->nullable();
                $table->string('color_from')->nullable();
                $table->string('color_to')->nullable();
                $table->timestamps();
            });
        }

        // 2) Tambah kolom FK di master_records (nullable dulu untuk backfill)
        if (!Schema::hasColumn('master_records', 'master_entity_id')) {
            Schema::table('master_records', function (Blueprint $t) {
                $t->uuid('master_entity_id')->nullable()->after('entity');
                $t->index(['master_entity_id', 'name']);
            });
        }

        // 3) Backfill: ambil distinct entity di master_records -> masukkan ke master_entities jika belum ada
        $keys = DB::table('master_records')
            ->select('entity')->whereNotNull('entity')
            ->distinct()->pluck('entity')->filter()->values();

        foreach ($keys as $i => $key) {
            $exists = DB::table('master_entities')->where('key', $key)->exists();
            if (!$exists) {
                DB::table('master_entities')->insert([
                    'id'         => (string) Str::uuid(),
                    'key'        => $key,
                    'label'      => Str::headline(str_replace('_', ' ', $key)),
                    'enabled'    => true,
                    'sort'       => $i,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // 4) Isi master_entity_id pada master_records berdasar key
        $map = DB::table('master_entities')->pluck('id', 'key'); // [key => uuid]
        foreach ($map as $key => $uuid) {
            DB::table('master_records')
                ->where('entity', $key)
                ->update(['master_entity_id' => $uuid]);
        }

        // 5) Jadikan NOT NULL + pasang FK
        Schema::table('master_records', function (Blueprint $t) {
            $t->uuid('master_entity_id')->nullable(false)->change();
            $t->foreign('master_entity_id')
                ->references('id')->on('master_entities')
                ->cascadeOnUpdate()
                ->restrictOnDelete(); // cegah hapus entity jika masih dipakai
        });
    }

    public function down(): void
    {
        // Lepas FK & kolom (data master_entities dibiarkan)
        Schema::table('master_records', function (Blueprint $t) {
            if (Schema::hasColumn('master_records', 'master_entity_id')) {
                $t->dropForeign(['master_entity_id']);
                $t->dropIndex(['master_entity_id', 'name']);
                $t->dropColumn('master_entity_id');
            }
        });
    }
};
