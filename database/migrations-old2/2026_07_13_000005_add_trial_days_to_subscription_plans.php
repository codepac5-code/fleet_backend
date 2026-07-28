<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function schema()
    {
        return Schema::connection('global')->hasTable('subscription_plans')
            ? Schema::connection('global')
            : Schema::connection(null);
    }

    public function up(): void
    {
        $schema = $this->schema();

        if (!$schema->hasTable('subscription_plans')) {
            return;
        }

        $schema->table('subscription_plans', function (Blueprint $table) {
            if (!Schema::connection($this->connectionName())->hasColumn('subscription_plans', 'trial_days')) {
                $table->unsignedInteger('trial_days')->nullable()->after('driver_limit');
            }
        });
    }

    public function down(): void
    {
        $schema = $this->schema();

        if (!$schema->hasTable('subscription_plans')) {
            return;
        }

        $schema->table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('trial_days');
        });
    }

    private function connectionName(): ?string
    {
        return Schema::connection('global')->hasTable('subscription_plans') ? 'global' : null;
    }
};
