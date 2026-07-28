<?php

use App\Http\Core\Const\Billing\BillingMode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function schema()
    {
        return Schema::connection('global')->hasTable('infrastructure_nodes')
            ? Schema::connection('global')
            : Schema::connection(null);
    }

    public function up(): void
    {
        $schema = $this->schema();

        if (!$schema->hasTable('infrastructure_nodes')) {
            return;
        }

        $schema->table('infrastructure_nodes', function (Blueprint $table) {
            if (!Schema::connection($this->connectionName())->hasColumn('infrastructure_nodes', 'billing_mode')) {
                $table->string('billing_mode', 16)->default(BillingMode::COMMISSION)->after('country_code');
            }
        });
    }

    public function down(): void
    {
        $schema = $this->schema();

        if (!$schema->hasTable('infrastructure_nodes')) {
            return;
        }

        $schema->table('infrastructure_nodes', function (Blueprint $table) {
            $table->dropColumn('billing_mode');
        });
    }

    private function connectionName(): ?string
    {
        return Schema::connection('global')->hasTable('infrastructure_nodes') ? 'global' : null;
    }
};
