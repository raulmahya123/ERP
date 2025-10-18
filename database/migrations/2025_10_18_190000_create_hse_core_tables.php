<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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

            $table->foreignUuid('site_id')->nullable()->constrained('sites')->nullOnDelete();

            $table->dateTime('sampled_at')->index();
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
        | KPI INDICATORS (Leading / Lagging / Operational)
        |--------------------------------------------------------------------------
        | Rekam indikator HSE per hari/bulan per site.
        */
        Schema::create('kpi_indicators', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('site_id')->nullable()->constrained('sites')->nullOnDelete();

            // Periode & tipe
            $table->date('date')->index();                                 // gunakan awal bulan bila bulanan
            $table->enum('type', ['leading','lagging','operational'])->index();

            // Indikator
            $table->string('name', 120)->index();                           // ex: Near Miss Reported, LTI, TRIFR, Safety Briefing
            $table->decimal('value', 14, 4)->default(0);
            $table->string('unit', 20)->nullable();                         // ex: count, %, rate

            $table->text('notes')->nullable();
            $table->json('meta')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['site_id','date','type','name'], 'uniq_kpi_site_date_type_name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_indicators');
        Schema::dropIfExists('media_attachments');
        Schema::dropIfExists('environmental_samples');
        Schema::dropIfExists('picas');              // FK ke hazard_reports
        Schema::dropIfExists('hazard_reports');     // harus setelah picas
        Schema::dropIfExists('incident_investigations');
        Schema::dropIfExists('incidents');
    }
};
