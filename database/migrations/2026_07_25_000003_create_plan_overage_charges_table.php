<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Accrued plan-overage charges (per-country shard). When an office exceeds its
 * subscription plan (extra driver / extra ride), the fee is ACCRUED here rather
 * than debited immediately — it is collected on the office's invoice. One row
 * per (type, reference) makes accrual idempotent (a ride/driver is charged once).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_overage_charges', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('office_id')->index();
            $table->string('period', 7);            // YYYY-MM billing period
            $table->string('type', 20);             // ride | driver
            $table->string('reference_type', 40)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->unsignedInteger('amount_minor');
            $table->string('currency_code', 3)->nullable();
            $table->string('status', 20)->default('pending'); // pending | invoiced | collected
            $table->string('invoice_ref', 60)->nullable();    // set at period closeout
            $table->string('collection_method', 20)->nullable(); // stripe | manual
            $table->string('external_ref', 120)->nullable();  // provider invoice-item id
            $table->timestamp('invoiced_at')->nullable();
            $table->timestamp('collected_at')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->unique(['type', 'reference_type', 'reference_id'], 'overage_ref_unique');
            $table->index(['office_id', 'period', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_overage_charges');
    }
};
