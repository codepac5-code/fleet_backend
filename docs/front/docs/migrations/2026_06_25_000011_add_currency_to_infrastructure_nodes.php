<?php

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
            if (!Schema::connection($this->connectionName())->hasColumn('infrastructure_nodes', 'currency_code')) {
                $table->string('currency_code', 3)->nullable()->after('country_code');
            }
            if (!Schema::connection($this->connectionName())->hasColumn('infrastructure_nodes', 'currency_symbol')) {
                $table->string('currency_symbol', 8)->nullable()->after('currency_code');
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
            $table->dropColumn(['currency_code', 'currency_symbol']);
        });
    }

    private function connectionName(): ?string
    {
        return Schema::connection('global')->hasTable('infrastructure_nodes') ? 'global' : null;
    }
};
