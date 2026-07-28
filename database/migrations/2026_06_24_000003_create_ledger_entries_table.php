<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ledger_entries')) {
            return;
        }

        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('transaction_id');
            $table->unsignedBigInteger('account_id');
            $table->string('direction', 6);
            $table->bigInteger('amount_minor');
            $table->string('currency_code', 10);
            $table->bigInteger('balance_after_minor')->nullable();
            $table->timestamps();

            $table->index('transaction_id');
            $table->index('account_id');

            $table->foreign('transaction_id')->references('id')->on('ledger_transactions')->onDelete('cascade');
            $table->foreign('account_id')->references('id')->on('ledger_accounts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_entries');
    }
};
