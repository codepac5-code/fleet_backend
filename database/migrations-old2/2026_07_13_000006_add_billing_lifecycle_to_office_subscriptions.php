<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('office_subscriptions')) {
            return;
        }

        Schema::table('office_subscriptions', function (Blueprint $table) {
            if (!Schema::hasColumn('office_subscriptions', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->after('started_at');
            }
            if (!Schema::hasColumn('office_subscriptions', 'current_period_end')) {
                $table->timestamp('current_period_end')->nullable()->after('trial_ends_at');
            }
            if (!Schema::hasColumn('office_subscriptions', 'cancel_at_period_end')) {
                $table->boolean('cancel_at_period_end')->default(false)->after('current_period_end');
            }
            if (!Schema::hasColumn('office_subscriptions', 'provider')) {
                $table->string('provider', 16)->nullable()->after('cancel_at_period_end');
            }
            if (!Schema::hasColumn('office_subscriptions', 'provider_customer_id')) {
                $table->string('provider_customer_id')->nullable()->after('provider');
            }
            if (!Schema::hasColumn('office_subscriptions', 'provider_subscription_id')) {
                $table->string('provider_subscription_id')->nullable()->after('provider_customer_id');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('office_subscriptions')) {
            return;
        }

        Schema::table('office_subscriptions', function (Blueprint $table) {
            $table->dropColumn([
                'trial_ends_at', 'current_period_end', 'cancel_at_period_end',
                'provider', 'provider_customer_id', 'provider_subscription_id',
            ]);
        });
    }
};
