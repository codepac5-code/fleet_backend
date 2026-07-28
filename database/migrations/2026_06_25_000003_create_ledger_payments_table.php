<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ledger_payments')) {
            return;
        }

        Schema::create('ledger_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('idempotency_key', 191)->unique();
            $table->string('provider', 32);
            $table->string('provider_ref', 191)->nullable();
            $table->string('kind', 16);
            $table->string('owner_type', 32);
            $table->unsignedBigInteger('owner_id');
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->bigInteger('amount_minor');
            $table->string('currency_code', 10);
            $table->string('status', 16)->default('pending');
            $table->uuid('ledger_transaction_uuid')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->unique(['provider', 'provider_ref'], 'ledger_payments_provider_ref_unique');
            $table->index(['owner_type', 'owner_id']);
            $table->index('booking_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_payments');
    }
};
