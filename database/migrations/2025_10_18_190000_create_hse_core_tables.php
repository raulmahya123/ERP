<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | INCIDENTS (laporan insiden)
        |--------------------------------------------------------------------------
        */
        Schema::create('incidents', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Konteks lokasi & pelapor
            $table->foreignUuid('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignUuid('reporter_id')->nullable()->constrained('users')->nullOnDelete();

            // Identitas & kronologi
            $table->string('code', 40)->unique();                 // ex: INC-DBK-2025-0012
            $table->dateTime('occurred_at')->index();             // kapan kejadian
            $table->string('location')->nullable();               // area/koordinat
            $table->string('category', 50)->nullable();           // ex: near miss, first aid, property damage, environmental
            $table->string('severity', 30)->nullable();           // ex: low/medium/high/fatal
            $table->text('description')->nullable();

            // Status siklus
            $table->enum('status', ['reported','under_investigation','action_in_progress','closed'])
                  ->default('reported')->index();

            // Opsional penandaan
            $table->json('tags')->nullable();                     // ["heavy equipment","contractor"] dst
            $table->json('meta')->nullable();                     // fleksibel

            $table->timestamps();
            $table->softDeletes();
        });

        /*
        |--------------------------------------------------------------------------
        | INCIDENT INVESTIGATIONS (investigasi insiden)
        |--------------------------------------------------------------------------
        */
        Schema::create('incident_investigations', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Kolom Code untuk list
            $table->string('code', 40)->unique();

            $table->foreignUuid('incident_id')->constrained('incidents')->cascadeOnDelete();
            $table->foreignUuid('lead_investigator_id')->nullable()->constrained('users')->nullOnDelete();

            $table->dateTime('started_at')->nullable()->index();
            $table->dateTime('completed_at')->nullable()->index();

            $table->string('method', 50)->nullable();             // ex: 5-Why, Fishbone, TapRoot
            $table->text('findings_summary')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('corrective_actions')->nullable();

            $table->enum('status', ['open','review','closed'])->default('open')->index();

            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        /*
        |--------------------------------------------------------------------------
        | HAZARD REPORTS (observasi bahaya / near miss) — LEADING
        |--------------------------------------------------------------------------
        | Dipisahkan dari incident agar workflow & KPI jelas (proaktif).
        */
        Schema::create('hazard_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Konteks & pelapor
            $table->foreignUuid('site_id')->nullable()->constrained('sites')->nullOnDelete();
            $table->foreignUuid('reporter_id')->nullable()->constrained('users')->nullOnDelete();

            // Identitas & detail
            $table->string('code', 40)->unique();              // HZR-DBK-2025-0001
            $table->dateTime('observed_at')->index();          // kapan bahaya diamati
            $table->string('location')->nullable();            // area/koordinat
            $table->string('category', 60)->nullable();        // housekeeping, traffic, electrical, etc.
            $table->text('description')->nullable();           // deskripsi bahaya
            $table->text('immediate_action')->nullable();      // tindakan segera
            $table->text('recommendation')->nullable();        // saran perbaikan

            // Penilaian risiko (awal & residual)
            $table->unsignedTinyInteger('likelihood_initial')->nullable(); // 1-5
            $table->unsignedTinyInteger('severity_initial')->nullable();   // 1-5
            $table->unsignedSmallInteger('risk_initial')->nullable()->index(); // LxS

            $table->unsignedTinyInteger('likelihood_residual')->nullable();
            $table->unsignedTinyInteger('severity_residual')->nullable();
            $table->unsignedSmallInteger('risk_residual')->nullable()->index();

            // Penanggung jawab & target
            $table->foreignUuid('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable()->index();

            // Keterkaitan opsional (jika hazard memunculkan incident)
            $table->foreignUuid('linked_incident_id')->nullable()->constrained('incidents')->nullOnDelete();

            // Status siklus
            $table->enum('status', ['reported','assigned','mitigated','verified','closed'])
                  ->default('reported')->index();

            $table->dateTime('verified_at')->nullable()->index();
            $table->foreignUuid('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('verification_note')->nullable();

            // Tag & meta fleksibel
            $table->json('tags')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['site_id','observed_at','status']);
            $table->index(['category','status']);
        });

        /*
        |--------------------------------------------------------------------------
        | PICA (Problem Identification & Preventive Action)
        |--------------------------------------------------------------------------
        | Bisa berdiri sendiri atau terhubung ke incident/hazard.
        */
        Schema::create('picas', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Code & reference
            $table->string('code', 40)->unique();
            $table->string('reference', 60)->nullable()->index(); // simpan kode referensi (INC-..., HZR-...)

            $table->foreignUuid('related_incident_id')->nullable()->constrained('incidents')->nullOnDelete();
            $table->foreignUuid('related_hazard_id')->nullable()->constrained('hazard_reports')->nullOnDelete();

            $table->string('title', 200);
            $table->text('problem_statement')->nullable();
            $table->text('root_cause')->nullable();
            $table->text('preventive_action')->nullable();

            $table->foreignUuid('owner_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('due_date')->nullable()->index();

            $table->enum('status', ['open','in_progress','pending_review','effective','ineffective','closed'])
                  ->default('open')->index();

            $table->dateTime('closed_at')->nullable()->index();
            $table->text('effectiveness_review')->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        /*
        |--------------------------------------------------------------------------
        | ENVIRONMENTAL SAMPLES (udara, emisi, kebisingan)
        |--------------------------------------------------------------------------
        */
        Schema::create('environmental_samples', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Code & Status
            $table->string('code', 40)->unique();

            $table->foreignUuid('site_id')->nullable()->constrained('sites')->nullOnDelete();

            $table->dateTime('sampled_at')->index();                // "Collected At" di UI
            $table->enum('type', ['air','emission','noise'])->index(); // udara, emisi, kebisingan
            $table->string('location')->nullable();

            // Parameter & hasil ukur
            $table->string('parameter')->index();                 // ex: PM2.5, SO2, NOx, dBA
            $table->decimal('value', 12, 4)->nullable();
            $table->string('unit', 20)->nullable();               // ex: µg/m3, ppm, dBA
            $table->string('method', 100)->nullable();            // SNI/US-EPA/ISO dsb.
            $table->string('instrument', 100)->nullable();        // alat
            $table->decimal('limit_value', 12, 4)->nullable();    // baku mutu
            $table->boolean('is_compliant')->nullable()->index(); // memenuhi baku mutu?

            // Status siklus untuk workflow verifikasi sampel
            $table->enum('status', ['draft','submitted','verified'])->default('draft')->index();

            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['site_id','sampled_at','type']);
        });

        /*
        |--------------------------------------------------------------------------
        | MEDIA ATTACHMENTS (foto/video evidence) — polymorphic
        |--------------------------------------------------------------------------
        | Dipakai untuk Incident, Investigation, PICA, Hazard, atau Sample.
        */
        Schema::create('media_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Polymorphic relation (sudah termasuk index komposit)
            $table->uuidMorphs('attachable'); // attachable_type, attachable_id (uuid) + INDEX
            $table->foreignUuid('uploaded_by')->nullable()->constrained('users')->nullOnDelete();

            $table->string('path');           // storage path
            $table->string('disk', 50)->default('public'); // opsional
            $table->string('mime', 100)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->dateTime('taken_at')->nullable()->index();
            $table->string('caption', 255)->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });

        /*
        |--------------------------------------------------------------------------
        | KPI DEFINITIONS (master indikator + threshold)
        |--------------------------------------------------------------------------
        */
        Schema::create('kpi_definitions', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->unsignedSmallInteger('order_no')->default(0)->index();
            $table->string('name', 160);
            $table->string('code', 40)->unique(); // ex: LTI, LTISR, SAP, MP, MH

            // Kelompok indikator
            $table->enum('group', ['leading','lagging','base','operational'])->index();

            // Jenis data: int, decimal, rate (ratio), currency
            $table->enum('value_type', ['int','decimal','rate','currency'])->default('int');

            // Agregasi default: SUM | MAX
            $table->enum('agg', ['SUM','MAX'])->default('SUM');

            // Unit tampilan (bebas)
            $table->string('unit', 50)->nullable();

            // Flag turunan (bukan input langsung)
            $table->boolean('is_derived')->default(false)->index();

            // Threshold bawaan
            $table->decimal('threshold_value', 18, 4)->nullable();
            $table->string('threshold_label', 50)->nullable();

            $table->json('meta')->nullable();

            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | KPI INDICATORS (time-series per site + def)
        |--------------------------------------------------------------------------
        | Simpan nilai per hari/bulan per site berdasarkan definition.
        */
        Schema::create('kpi_indicators', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('site_id')->nullable()->constrained('sites')->nullOnDelete();

            // relasi master KPI definition
            $table->foreignUuid('definition_id')->nullable()->constrained('kpi_definitions')->nullOnDelete();

            // Periode & (legacy) tipe
            $table->date('date')->index(); // gunakan awal bulan bila bulanan

            // Legacy kolom agar kompatibel (boleh kamu hapus nanti setelah full pakai definition_id)
            $table->enum('type', ['leading','lagging','operational'])->index();
            $table->string('name', 120)->index();     // ex: Near Miss Reported, LTI, dll
            $table->string('unit', 20)->nullable();   // ex: count, %, rate

            // Nilai
            $table->decimal('value', 14, 4)->default(0);

            $table->text('notes')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Unique lama (legacy)
            $table->unique(['site_id','date','type','name'], 'uniq_kpi_site_date_type_name');
            // Unique baru berbasis definition
            $table->unique(['site_id','date','definition_id'], 'uniq_kpi_site_date_definition');
        });

        /*
        |--------------------------------------------------------------------------
        | Seed definisi KPI (opsional tapi praktis)
        |--------------------------------------------------------------------------
        */
        $defs = [
            // Lagging
            ['o'=>1,  'n'=>'Fatality', 'c'=>'FTL',    'g'=>'lagging','t'=>'int',     'a'=>'SUM','u'=>'Kasus','d'=>false,'th'=>'0'],
            ['o'=>2,  'n'=>'LTI (Lost Time Injury)', 'c'=>'LTI',    'g'=>'lagging','t'=>'int',     'a'=>'SUM','u'=>'Kasus','d'=>false,'th'=>'0'],
            ['o'=>3,  'n'=>'LTI SR',                 'c'=>'LTISR',  'g'=>'lagging','t'=>'rate',    'a'=>'SUM','u'=>'Ratio','d'=>false,'th'=>'0'],
            ['o'=>4,  'n'=>'Injury Non LTI',         'c'=>'INLTI',  'g'=>'lagging','t'=>'int',     'a'=>'SUM','u'=>'Kasus','d'=>false,'th'=>'0'],
            ['o'=>5,  'n'=>'Injury non LTI FR',      'c'=>'INLTIFR','g'=>'lagging','t'=>'decimal', 'a'=>'SUM','u'=>'Ratio','d'=>false,'th'=>'2,13'],
            ['o'=>6,  'n'=>'PD (Property Damage)',    'c'=>'PD',     'g'=>'lagging','t'=>'int',     'a'=>'SUM','u'=>'Kasus','d'=>false,'th'=>'0'],
            ['o'=>7,  'n'=>'PDFR (Property Damage Frequency Rate)','c'=>'PDFR','g'=>'lagging','t'=>'decimal','a'=>'SUM','u'=>'Ratio','d'=>false,'th'=>'6,91'],
            ['o'=>8,  'n'=>'PD Cost',                'c'=>'PDC',    'g'=>'lagging','t'=>'currency','a'=>'SUM','u'=>'$','d'=>false,'th'=>'$5000'],
            ['o'=>9,  'n'=>'PAK ( Penyakit Akibat Kerja)','c'=>'PAK','g'=>'lagging','t'=>'int','a'=>'SUM','u'=>'Kasus','d'=>false,'th'=>'0'],
            ['o'=>10, 'n'=>'KAPTK (Kejadian Akibat Penyakit Tenaga Kerja)','c'=>'KAPTK','g'=>'lagging','t'=>'int','a'=>'SUM','u'=>'Kasus','d'=>false,'th'=>'0'],
            ['o'=>11, 'n'=>'Rasio Kelaikan Kerja (RKK)', 'c'=>'RKK', 'g'=>'lagging','t'=>'decimal','a'=>'SUM','u'=>'Percentage','d'=>false,'th'=>'100%'],
            ['o'=>12, 'n'=>'Absence Severity Rate (ASR)','c'=>'ASR', 'g'=>'lagging','t'=>'decimal','a'=>'SUM','u'=>'Rasio','d'=>false,'th'=>'300'],
            ['o'=>13, 'n'=>'Enviro Accident',        'c'=>'EA',     'g'=>'lagging','t'=>'int',     'a'=>'SUM','u'=>'Kasus','d'=>false,'th'=>'0'],
            ['o'=>14, 'n'=>'Morbidity Fraquency Rate (MFR)','c'=>'MFR','g'=>'lagging','t'=>'decimal','a'=>'SUM','u'=>'ratio','d'=>false,'th'=>'400'],
            ['o'=>15, 'n'=>'Near Miss',              'c'=>'NM',     'g'=>'lagging','t'=>'int',     'a'=>'SUM','u'=>'Kasus','d'=>false,'th'=>'0'],

            // Leading
            ['o'=>1,  'n'=>'Safety Accountability Program (SAP)','c'=>'SAP','g'=>'leading','t'=>'rate','a'=>'SUM','u'=>'Persentage','d'=>false,'th'=>'0'],
            ['o'=>2,  'n'=>'Kondisi Tidak Aman (KTA)','c'=>'KTA','g'=>'leading','t'=>'int','a'=>'SUM','u'=>'Kasus','d'=>false,'th'=>'0'],
            ['o'=>3,  'n'=>'Tindakan Tidak Aman (TTA)','c'=>'TTA','g'=>'leading','t'=>'int','a'=>'SUM','u'=>'Kasus','d'=>false,'th'=>'0'],
            ['o'=>4,  'n'=>'Safety Inspection','c'=>'SI','g'=>'leading','t'=>'int','a'=>'SUM','u'=>'Kasus','d'=>false,'th'=>'0'],
            ['o'=>5,  'n'=>'Planned Task Observation (PTO)','c'=>'PTO','g'=>'leading','t'=>'int','a'=>'SUM','u'=>'Kasus','d'=>false,'th'=>'0'],
            ['o'=>6,  'n'=>'Tindak Lanjut PICA','c'=>'TLP','g'=>'leading','t'=>'decimal','a'=>'SUM','u'=>'Ratio','d'=>false,'th'=>'100%'],
            ['o'=>7,  'n'=>'Legal Compliance','c'=>'LC','g'=>'leading','t'=>'decimal','a'=>'SUM','u'=>'Presentase','d'=>false,'th'=>'90%'],
            ['o'=>8,  'n'=>'Implementasi Program Kerja','c'=>'IPK','g'=>'leading','t'=>'decimal','a'=>'MAX','u'=>'Presentase','d'=>false,'th'=>'100%'],
            ['o'=>9,  'n'=>'Training SHE','c'=>'TS','g'=>'leading','t'=>'decimal','a'=>'MAX','u'=>'Presentase','d'=>false,'th'=>'100%'],
            ['o'=>10, 'n'=>'Audit SMKP Score','c'=>'ASS','g'=>'leading','t'=>'decimal','a'=>'MAX','u'=>'Presentase','d'=>false,'th'=>'62%'],

            // Base
            ['o'=>1,  'n'=>'Man Power','c'=>'MP','g'=>'base','t'=>'int','a'=>'SUM','u'=>null,'d'=>false,'th'=>'0'],
            ['o'=>2,  'n'=>'Man Hours','c'=>'MH','g'=>'base','t'=>'int','a'=>'SUM','u'=>null,'d'=>false,'th'=>'0'],
        ];

        foreach ($defs as $r) {
            [$val, $label] = (function (?string $s) {
                if ($s === null || $s === '') return [null, null];
                $label = trim($s);
                if (str_ends_with($label, '%')) {
                    $n = rtrim($label, '%');
                    $n = str_replace([',',' '], ['.',''], $n);
                    return [is_numeric($n) ? (float) $n : null, $label];
                }
                if (preg_match('/^[^\d\-]*\s*\d[\d,.\s]*$/', $label)) {
                    $n = preg_replace('/[^\d,.\-]/', '', $label);
                    $n = str_replace([',',' '], ['.',''], $n);
                    return [is_numeric($n) ? (float) $n : null, $label];
                }
                $n = str_replace([',',' '], ['.',''], $label);
                return [is_numeric($n) ? (float) $n : null, $label];
            })($r['th']);

            DB::table('kpi_definitions')->updateOrInsert(
                ['code' => strtoupper($r['c'])],
                [
                    'id'              => (string) Str::uuid(),
                    'order_no'        => (int) $r['o'],
                    'name'            => $r['n'],
                    'code'            => strtoupper($r['c']),
                    'group'           => $r['g'],
                    'value_type'      => match ($r['t']) {
                        'int' => 'int', 'decimal' => 'decimal', 'rate' => 'rate', 'currency' => 'currency',
                        default => 'int',
                    },
                    'agg'             => strtoupper($r['a']),
                    'unit'            => $r['u'],
                    'is_derived'      => (bool) $r['d'],
                    'threshold_value' => $val,
                    'threshold_label' => $label,
                    'meta'            => null,
                    'created_at'      => now(),
                    'updated_at'      => now(),
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_indicators');
        Schema::dropIfExists('kpi_definitions');
        Schema::dropIfExists('media_attachments');
        Schema::dropIfExists('environmental_samples');
        Schema::dropIfExists('picas');              // FK ke hazard_reports
        Schema::dropIfExists('hazard_reports');     // harus setelah picas
        Schema::dropIfExists('incident_investigations');
        Schema::dropIfExists('incidents');
    }
};
