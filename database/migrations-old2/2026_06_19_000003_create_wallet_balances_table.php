<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('wallet_balances')) {
            return;
        }

        Schema::create('wallet_balances', function (Blueprint $table) {
            $table->id();
            $table->string('owner_type');
            $table->unsignedBigInteger('owner_id');
            $table->string('currency_code', 10);
            $table->decimal('balance', 18, 2)->default(0);
            $table->timestamps();

            $table->unique(['owner_type', 'owner_id', 'currency_code']);
            $table->index(['owner_type', 'owner_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallet_balances');
    }
};
