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

        $connection = $schema->getConnection()->getName();

        $schema->table('subscription_plans', function (Blueprint $table) use ($connection) {
            if (!Schema::connection($connection)->hasColumn('subscription_plans', 'is_popular')) {
                $table->boolean('is_popular')->default(false)->after('is_active');
            }
            if (!Schema::connection($connection)->hasColumn('subscription_plans', 'features')) {
                $table->json('features')->nullable()->after('is_popular');
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
            $table->dropColumn(['is_popular', 'features']);
        });
    }
};
