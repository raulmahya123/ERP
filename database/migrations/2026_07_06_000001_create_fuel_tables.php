<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fuel_tanks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->string('code', 50)->unique();
            $table->string('name', 200);
            $table->string('fuel_type', 50)->default('diesel');
            $table->decimal('capacity', 15, 2)->default(0);
            $table->decimal('current_volume', 15, 2)->default(0);
            $table->string('location', 200)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fuel_flow_meters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->string('code', 50)->unique();
            $table->string('name', 200);
            $table->foreignUuid('tank_id')->nullable()->constrained('fuel_tanks');
            $table->decimal('meter_reading', 15, 2)->default(0);
            $table->string('uom', 20)->default('liter');
            $table->string('location', 200)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fuel_consumes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->foreignUuid('tank_id')->nullable()->constrained('fuel_tanks');
            $table->foreignUuid('flow_meter_id')->nullable()->constrained('fuel_flow_meters');
            $table->foreignUuid('unit_id')->nullable()->constrained('assets');
            $table->foreignUuid('operator_id')->nullable()->constrained('users');
            $table->dateTime('consume_at');
            $table->decimal('volume', 15, 2);
            $table->string('fuel_type', 50)->default('diesel');
            $table->decimal('meter_start', 15, 2)->nullable();
            $table->decimal('meter_end', 15, 2)->nullable();
            $table->string('reference_no', 100)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fuel_receives', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->foreignUuid('tank_id')->nullable()->constrained('fuel_tanks');
            $table->string('supplier', 200)->nullable();
            $table->string('po_number', 100)->nullable();
            $table->string('invoice_number', 100)->nullable();
            $table->dateTime('receive_at');
            $table->decimal('volume', 15, 2);
            $table->string('fuel_type', 50)->default('diesel');
            $table->decimal('price_per_unit', 15, 2)->nullable();
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->string('vehicle_number', 50)->nullable();
            $table->string('driver_name', 100)->nullable();
            $table->string('reference_no', 100)->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fuel_stock_checks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->foreignUuid('tank_id')->constrained('fuel_tanks');
            $table->dateTime('check_at');
            $table->decimal('book_volume', 15, 2);
            $table->decimal('actual_volume', 15, 2);
            $table->decimal('difference', 15, 2);
            $table->string('uom', 20)->default('liter');
            $table->text('notes')->nullable();
            $table->foreignUuid('checked_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('fuel_inventory_balances', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->foreignUuid('tank_id')->constrained('fuel_tanks');
            $table->date('balance_date');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('receive_qty', 15, 2)->default(0);
            $table->decimal('consume_qty', 15, 2)->default(0);
            $table->decimal('adjustment_qty', 15, 2)->default(0);
            $table->decimal('closing_balance', 15, 2)->default(0);
            $table->string('fuel_type', 50)->default('diesel');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'tank_id', 'balance_date']);
        });

        Schema::create('fuel_postings', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->string('posting_type', 50);
            $table->string('reference_type', 50)->nullable();
            $table->string('reference_id', 36)->nullable();
            $table->date('posting_date');
            $table->string('description', 500)->nullable();
            $table->string('status', 20)->default('draft');
            $table->json('journal_entries')->nullable();
            $table->foreignUuid('posted_by')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('fuel_adjustments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->foreignUuid('tank_id')->constrained('fuel_tanks');
            $table->dateTime('adjustment_at');
            $table->decimal('volume', 15, 2);
            $table->string('adjustment_type', 20);
            $table->string('reason', 500)->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignUuid('requested_by')->nullable()->constrained('users');
            $table->foreignUuid('approved_by')->nullable()->constrained('users');
            $table->dateTime('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('fuel_adjustment_approvals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('adjustment_id')->constrained('fuel_adjustments');
            $table->foreignUuid('approver_id')->constrained('users');
            $table->string('status', 20)->default('pending');
            $table->text('notes')->nullable();
            $table->dateTime('action_at')->nullable();
            $table->timestamps();
        });

        Schema::create('fuel_tank_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('site_id')->constrained('sites');
            $table->foreignUuid('tank_id')->constrained('fuel_tanks');
            $table->string('transaction_type', 50);
            $table->string('reference_type', 50)->nullable();
            $table->string('reference_id', 36)->nullable();
            $table->decimal('volume_in', 15, 2)->default(0);
            $table->decimal('volume_out', 15, 2)->default(0);
            $table->decimal('balance_before', 15, 2)->default(0);
            $table->decimal('balance_after', 15, 2)->default(0);
            $table->dateTime('transaction_at');
            $table->text('description')->nullable();
            $table->foreignUuid('created_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_tank_histories');
        Schema::dropIfExists('fuel_adjustment_approvals');
        Schema::dropIfExists('fuel_adjustments');
        Schema::dropIfExists('fuel_postings');
        Schema::dropIfExists('fuel_inventory_balances');
        Schema::dropIfExists('fuel_stock_checks');
        Schema::dropIfExists('fuel_receives');
        Schema::dropIfExists('fuel_consumes');
        Schema::dropIfExists('fuel_flow_meters');
        Schema::dropIfExists('fuel_tanks');
    }
};
