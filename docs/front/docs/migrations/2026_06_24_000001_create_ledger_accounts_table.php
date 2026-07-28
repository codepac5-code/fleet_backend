<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ledger_accounts')) {
            return;
        }

        Schema::create('ledger_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('owner_type', 191);
            $table->unsignedBigInteger('owner_id');
            $table->string('account_type', 32);
            $table->string('currency_code', 10);
            $table->bigInteger('balance_minor')->default(0);
            $table->string('code', 64)->nullable();
            $table->timestamps();

            $table->unique(['owner_type', 'owner_id', 'account_type', 'currency_code'], 'ledger_acct_unique');
            $table->index(['owner_type', 'owner_id']);
            $table->index(['account_type', 'currency_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ledger_accounts');
    }
};
