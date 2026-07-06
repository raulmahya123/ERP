<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ========== HSE ADDITIONS ==========
        Schema::create('hazard_areas', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->string('code', 50)->unique();
            $table->string('name', 200);
            $table->text('description')->nullable();
            $table->string('location', 200)->nullable();
            $table->string('risk_level', 20)->default('medium');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hse_rtp', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('hazard_report_id')->nullable()->constrained('hazard_reports');
            $table->foreignUuid('site_id')->constrained('sites');
            $table->string('rtp_number', 50)->unique();
            $table->text('corrective_action');
            $table->text('preventive_action')->nullable();
            $table->string('pic', 200)->nullable();
            $table->date('target_date')->nullable();
            $table->date('completion_date')->nullable();
            $table->string('status', 20)->default('open');
            $table->text('notes')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('hse_inspection_reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->string('report_number', 50)->unique();
            $table->string('inspection_type', 100);
            $table->string('location', 200)->nullable();
            $table->date('inspection_date');
            $table->text('findings')->nullable();
            $table->text('recommendations')->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignUuid('inspector_id')->nullable()->constrained('users');
            $table->foreignUuid('verified_by')->nullable()->constrained('users');
            $table->dateTime('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // ========== PRODUCTION CONTROL ==========
        Schema::create('production_monthly_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->string('plan_number', 50)->unique();
            $table->integer('year');
            $table->integer('month');
            $table->decimal('target_volume', 15, 2)->default(0);
            $table->string('uom', 20)->default('ton');
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['site_id', 'year', 'month']);
        });

        Schema::create('production_shift_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->foreignUuid('monthly_plan_id')->nullable()->constrained('production_monthly_plans');
            $table->date('plan_date');
            $table->string('shift', 20);
            $table->decimal('target_volume', 15, 2)->default(0);
            $table->decimal('target_ob', 15, 2)->default(0);
            $table->string('uom', 20)->default('bcm');
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('production_actuals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->foreignUuid('shift_plan_id')->nullable()->constrained('production_shift_plans');
            $table->date('actual_date');
            $table->string('shift', 20);
            $table->decimal('volume', 15, 2)->default(0);
            $table->decimal('ob_volume', 15, 2)->default(0);
            $table->decimal('waste_volume', 15, 2)->default(0);
            $table->decimal('overburden_volume', 15, 2)->default(0);
            $table->string('uom', 20)->default('bcm');
            $table->text('notes')->nullable();
            $table->foreignUuid('recorded_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('production_reconciles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->date('reconcile_date');
            $table->decimal('plan_volume', 15, 2)->default(0);
            $table->decimal('actual_volume', 15, 2)->default(0);
            $table->decimal('variance', 15, 2)->default(0);
            $table->decimal('variance_pct', 8, 2)->default(0);
            $table->text('notes')->nullable();
            $table->foreignUuid('reconciled_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('production_shift_closings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->date('close_date');
            $table->string('shift', 20);
            $table->dateTime('closed_at');
            $table->boolean('is_unlocked')->default(false);
            $table->dateTime('unlocked_at')->nullable();
            $table->foreignUuid('closed_by')->nullable()->constrained('users');
            $table->foreignUuid('unlocked_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['site_id', 'close_date', 'shift']);
        });

        Schema::create('production_monthly_closings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->integer('year');
            $table->integer('month');
            $table->dateTime('closed_at');
            $table->boolean('is_unlocked')->default(false);
            $table->foreignUuid('closed_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->unique(['site_id', 'year', 'month']);
        });

        // ========== HR ADDITIONS ==========
        Schema::create('recruitment_candidates', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('candidate_number', 50)->unique();
            $table->string('full_name', 200);
            $table->string('email', 200)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('position_applied', 200);
            $table->text('address')->nullable();
            $table->string('education', 200)->nullable();
            $table->text('experience')->nullable();
            $table->string('status', 20)->default('new');
            $table->text('notes')->nullable();
            $table->string('resume_file', 500)->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('recruitment_applicants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('candidate_id')->constrained('recruitment_candidates');
            $table->date('application_date');
            $table->string('source', 100)->nullable();
            $table->decimal('expected_salary', 15, 2)->nullable();
            $table->string('status', 20)->default('applied');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('recruitment_manpower_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->string('request_number', 50)->unique();
            $table->string('position', 200);
            $table->integer('quantity')->default(1);
            $table->date('required_date');
            $table->text('justification')->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignUuid('requested_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('recruitment_manpower_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('manpower_request_id')->constrained('recruitment_manpower_requests');
            $table->foreignUuid('approver_id')->constrained('users');
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->dateTime('action_at')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_movement_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('users');
            $table->string('movement_type', 50);
            $table->string('from_position', 200)->nullable();
            $table->string('to_position', 200)->nullable();
            $table->string('from_department', 200)->nullable();
            $table->string('to_department', 200)->nullable();
            $table->string('from_location', 200)->nullable();
            $table->string('to_location', 200)->nullable();
            $table->date('effective_date');
            $table->text('reason')->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignUuid('requested_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('employee_movements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('request_id')->nullable()->constrained('employee_movement_requests');
            $table->foreignUuid('employee_id')->constrained('users');
            $table->string('movement_type', 50);
            $table->text('old_data')->nullable();
            $table->text('new_data')->nullable();
            $table->date('effective_date');
            $table->foreignUuid('approved_by')->nullable()->constrained('users');
            $table->dateTime('executed_at');
            $table->timestamps();
        });

        Schema::create('employee_movement_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('movement_request_id')->constrained('employee_movement_requests');
            $table->foreignUuid('approver_id')->constrained('users');
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->dateTime('action_at')->nullable();
            $table->timestamps();
        });

        Schema::create('employee_benefits', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->string('benefit_code', 50)->unique();
            $table->string('benefit_name', 200);
            $table->string('benefit_type', 50);
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('employee_benefit_claims', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('employee_id')->constrained('users');
            $table->foreignUuid('benefit_id')->constrained('employee_benefits');
            $table->date('claim_date');
            $table->decimal('amount', 15, 2);
            $table->text('description')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('employee_benefit_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('claim_id')->constrained('employee_benefit_claims');
            $table->foreignUuid('approver_id')->constrained('users');
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->dateTime('action_at')->nullable();
            $table->timestamps();
        });

        // ========== ASSET MANAGEMENT (ARR/AER/DI) ==========
        Schema::create('asset_arr_masters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->string('arr_number', 50)->unique();
            $table->foreignUuid('asset_id')->constrained('assets');
            $table->date('request_date');
            $table->string('arr_type', 50);
            $table->text('reason')->nullable();
            $table->string('status', 20)->default('draft');
            $table->text('notes')->nullable();
            $table->foreignUuid('requested_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('asset_arr_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('arr_id')->constrained('asset_arr_masters');
            $table->foreignUuid('approver_id')->constrained('users');
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->dateTime('action_at')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_aer_masters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->string('aer_number', 50)->unique();
            $table->foreignUuid('asset_id')->constrained('assets');
            $table->date('request_date');
            $table->date('estimated_return_date')->nullable();
            $table->text('reason')->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignUuid('requested_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('asset_aer_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('aer_id')->constrained('asset_aer_masters');
            $table->foreignUuid('approver_id')->constrained('users');
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->dateTime('action_at')->nullable();
            $table->timestamps();
        });

        Schema::create('asset_delivery_instructions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->string('di_number', 50)->unique();
            $table->foreignUuid('asset_id')->constrained('assets');
            $table->date('delivery_date');
            $table->string('from_location', 200)->nullable();
            $table->string('to_location', 200)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignUuid('requested_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('asset_di_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('di_id')->constrained('asset_delivery_instructions');
            $table->foreignUuid('approver_id')->constrained('users');
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->dateTime('action_at')->nullable();
            $table->timestamps();
        });

        // ========== PLANT MODULE ==========
        Schema::create('plant_standard_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('job_code', 50)->unique();
            $table->string('job_name', 200);
            $table->foreignUuid('equipment_class_id')->nullable()->constrained('master_records');
            $table->text('description')->nullable();
            $table->integer('estimated_duration')->nullable();
            $table->string('duration_uom', 20)->default('hour');
            $table->string('maintenance_type', 50)->nullable();
            $table->text('safety_notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('plant_strategi_tasks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->string('task_code', 50)->unique();
            $table->string('task_name', 200);
            $table->string('task_type', 50);
            $table->string('frequency', 50)->nullable();
            $table->integer('interval_value')->nullable();
            $table->string('interval_uom', 20)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('plant_notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->string('notification_type', 50);
            $table->string('title', 200);
            $table->text('message')->nullable();
            $table->foreignUuid('asset_id')->nullable()->constrained('assets');
            $table->string('priority', 20)->default('normal');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->foreignUuid('recipient_id')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('plant_work_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->string('wo_number', 50)->unique();
            $table->foreignUuid('asset_id')->constrained('assets');
            $table->string('wo_type', 50);
            $table->string('priority', 20)->default('normal');
            $table->text('description')->nullable();
            $table->date('planned_start')->nullable();
            $table->date('planned_end')->nullable();
            $table->date('actual_start')->nullable();
            $table->date('actual_end')->nullable();
            $table->string('status', 20)->default('draft');
            $table->text('notes')->nullable();
            $table->foreignUuid('requested_by')->nullable()->constrained('users');
            $table->foreignUuid('assigned_to')->nullable()->constrained('users');
            $table->foreignUuid('approved_by')->nullable()->constrained('users');
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('plant_long_term_plannings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->foreignUuid('asset_id')->constrained('assets');
            $table->integer('year');
            $table->string('plan_type', 50);
            $table->text('description')->nullable();
            $table->date('planned_date')->nullable();
            $table->decimal('estimated_cost', 15, 2)->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('plant_breakdown_statuses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->foreignUuid('asset_id')->constrained('assets');
            $table->dateTime('breakdown_start');
            $table->dateTime('breakdown_end')->nullable();
            $table->string('breakdown_code', 50)->nullable();
            $table->text('description')->nullable();
            $table->string('status', 20)->default('open');
            $table->text('root_cause')->nullable();
            $table->text('action_taken')->nullable();
            $table->foreignUuid('reported_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('plant_wo_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('wo_id')->constrained('plant_work_orders');
            $table->foreignUuid('approver_id')->constrained('users');
            $table->string('approval_level', 20)->default('level1');
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->dateTime('action_at')->nullable();
            $table->timestamps();
        });

        Schema::create('plant_picklists', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->foreignUuid('wo_id')->constrained('plant_work_orders');
            $table->foreignUuid('material_id')->constrained('master_records');
            $table->decimal('quantity_required', 15, 2)->default(0);
            $table->decimal('quantity_issued', 15, 2)->default(0);
            $table->string('uom', 20)->default('pcs');
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestamps();
        });

        // ========== SUPPLY CHAIN ADDITIONS ==========
        Schema::create('scm_purchase_info_records', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->foreignUuid('material_id')->constrained('master_records');
            $table->foreignUuid('vendor_id')->constrained('master_records');
            $table->string('info_category', 20)->default('standard');
            $table->decimal('price', 15, 2);
            $table->string('currency', 10)->default('IDR');
            $table->string('uom', 20)->default('pcs');
            $table->decimal('min_order_qty', 15, 2)->nullable();
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->string('status', 20)->default('active');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('scm_material_masters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('material_code', 50)->unique();
            $table->string('material_name', 200);
            $table->string('material_type', 50)->nullable();
            $table->string('material_group', 50)->nullable();
            $table->string('base_uom', 20)->default('pcs');
            $table->decimal('weight', 15, 2)->nullable();
            $table->decimal('volume', 15, 2)->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('scm_purchase_requests', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->string('pr_number', 50)->unique();
            $table->date('request_date');
            $table->text('description')->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignUuid('requested_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('scm_purchase_request_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_request_id')->constrained('scm_purchase_requests');
            $table->foreignUuid('material_id')->constrained('scm_material_masters');
            $table->decimal('quantity', 15, 2);
            $table->string('uom', 20)->default('pcs');
            $table->date('required_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('scm_purchase_orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->string('po_number', 50)->unique();
            $table->foreignUuid('vendor_id')->constrained('master_records');
            $table->date('order_date');
            $table->date('delivery_date')->nullable();
            $table->string('payment_terms', 100)->nullable();
            $table->string('shipping_method', 100)->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('currency', 10)->default('IDR');
            $table->string('status', 20)->default('draft');
            $table->text('notes')->nullable();
            $table->foreignUuid('approved_by')->nullable()->constrained('users');
            $table->dateTime('approved_at')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('scm_purchase_order_items', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('purchase_order_id')->constrained('scm_purchase_orders');
            $table->foreignUuid('material_id')->constrained('scm_material_masters');
            $table->decimal('quantity', 15, 2);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total_price', 15, 2);
            $table->string('uom', 20)->default('pcs');
            $table->date('delivery_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('scm_reservations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->string('reservation_number', 50)->unique();
            $table->foreignUuid('material_id')->constrained('scm_material_masters');
            $table->decimal('quantity', 15, 2);
            $table->string('uom', 20)->default('pcs');
            $table->string('reservation_type', 50);
            $table->string('movement_type', 50)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignUuid('requested_by')->nullable()->constrained('users');
            $table->foreignUuid('approved_by')->nullable()->constrained('users');
            $table->dateTime('approved_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('scm_vhs_settlements', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->string('settlement_number', 50)->unique();
            $table->foreignUuid('purchase_order_id')->constrained('scm_purchase_orders');
            $table->decimal('total_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0);
            $table->decimal('balance', 15, 2)->default(0);
            $table->date('settlement_date');
            $table->string('status', 20)->default('draft');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('scm_vendor_evaluations', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->foreignUuid('vendor_id')->constrained('master_records');
            $table->string('evaluation_period', 50);
            $table->decimal('quality_score', 5, 2)->nullable();
            $table->decimal('delivery_score', 5, 2)->nullable();
            $table->decimal('price_score', 5, 2)->nullable();
            $table->decimal('service_score', 5, 2)->nullable();
            $table->decimal('total_score', 5, 2)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('scm_vendor_evaluation_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('evaluation_id')->constrained('scm_vendor_evaluations');
            $table->foreignUuid('approver_id')->constrained('users');
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->dateTime('action_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scm_vendor_evaluation_approvals');
        Schema::dropIfExists('scm_vendor_evaluations');
        Schema::dropIfExists('scm_vhs_settlements');
        Schema::dropIfExists('scm_reservations');
        Schema::dropIfExists('scm_purchase_order_items');
        Schema::dropIfExists('scm_purchase_orders');
        Schema::dropIfExists('scm_purchase_request_items');
        Schema::dropIfExists('scm_purchase_requests');
        Schema::dropIfExists('scm_material_masters');
        Schema::dropIfExists('scm_purchase_info_records');
        Schema::dropIfExists('plant_picklists');
        Schema::dropIfExists('plant_wo_approvals');
        Schema::dropIfExists('plant_breakdown_statuses');
        Schema::dropIfExists('plant_long_term_plannings');
        Schema::dropIfExists('plant_work_orders');
        Schema::dropIfExists('plant_notifications');
        Schema::dropIfExists('plant_strategi_tasks');
        Schema::dropIfExists('plant_standard_jobs');
        Schema::dropIfExists('asset_di_approvals');
        Schema::dropIfExists('asset_delivery_instructions');
        Schema::dropIfExists('asset_aer_approvals');
        Schema::dropIfExists('asset_aer_masters');
        Schema::dropIfExists('asset_arr_approvals');
        Schema::dropIfExists('asset_arr_masters');
        Schema::dropIfExists('employee_benefit_approvals');
        Schema::dropIfExists('employee_benefit_claims');
        Schema::dropIfExists('employee_benefits');
        Schema::dropIfExists('employee_movement_approvals');
        Schema::dropIfExists('employee_movements');
        Schema::dropIfExists('employee_movement_requests');
        Schema::dropIfExists('recruitment_manpower_approvals');
        Schema::dropIfExists('recruitment_manpower_requests');
        Schema::dropIfExists('recruitment_applicants');
        Schema::dropIfExists('recruitment_candidates');
        Schema::dropIfExists('production_monthly_closings');
        Schema::dropIfExists('production_shift_closings');
        Schema::dropIfExists('production_reconciles');
        Schema::dropIfExists('production_actuals');
        Schema::dropIfExists('production_shift_plans');
        Schema::dropIfExists('production_monthly_plans');
        Schema::dropIfExists('hse_inspection_reports');
        Schema::dropIfExists('hse_rtp');
        Schema::dropIfExists('hazard_areas');
    }
};
