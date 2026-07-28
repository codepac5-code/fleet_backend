<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('office_subscriptions')) {
            return;
        }

        Schema::create('office_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('office_id');
            $table->string('plan_key', 32);
            $table->decimal('fleet_commission_rate', 7, 4);
            $table->decimal('office_commission_rate', 7, 4)->default(0);
            $table->bigInteger('price_minor')->default(0);
            $table->string('currency_code', 10)->nullable();
            $table->string('status', 16)->default('active');
            $table->timestamp('started_at')->nullable();
            $table->date('period_end')->nullable();
            $table->timestamps();

            $table->index(['office_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('office_subscriptions');
    }
};
