<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payout_requests')) {
            return;
        }

        Schema::create('payout_requests', function (Blueprint $table) {
            $table->id();
            $table->string('owner_type');
            $table->unsignedBigInteger('owner_id');
            $table->string('source_account');
            $table->unsignedBigInteger('amount_minor');
            $table->string('currency_code', 3);
            $table->string('status')->default('pending');
            $table->string('idempotency_key')->nullable()->unique();
            $table->string('ledger_transaction_uuid')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
            $table->index(['owner_type', 'owner_id']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_requests');
    }
};
