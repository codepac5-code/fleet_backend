<?php

namespace Database\Seeders\Production;

use App\Http\Core\Const\Billing\BillingMode;
use App\Models\InfrastructureNode;
use Illuminate\Database\Seeder;

class InfrastructureNodeSeeder extends Seeder
{
    public function run(): void
    {
        $db = $this->primaryDbConfig();

        InfrastructureNode::query()->firstOrCreate(
            ['type' => 'country', 'country_code' => env('SEED_COUNTRY_CODE', 'SY')],
            [
                'name' => env('SEED_COUNTRY_NAME', 'Syria'),
                'billing_mode' => env('SEED_BILLING_MODE', BillingMode::COMMISSION),
                'currency_code' => env('SEED_CURRENCY_CODE', 'USD'),
                'currency_symbol' => env('SEED_CURRENCY_SYMBOL', '$'),
                'city' => env('SEED_COUNTRY_CITY', 'Damascus'),
                'lat' => env('SEED_COUNTRY_LAT', 33.5138),
                'lng' => env('SEED_COUNTRY_LNG', 36.2765),
                'radius_km' => 60,
                'db_host' => $db['host'] ?? '127.0.0.1',
                'db_name' => $db['database'] ?? '',
                'db_user' => $db['username'] ?? '',
                'db_pass' => $db['password'] ?? '',
                'db_port' => (string) ($db['port'] ?? '3306'),
                'redis_host' => env('REDIS_HOST', '127.0.0.1'),
                'redis_db' => env('SEED_COUNTRY_REDIS_DB', '1'),
                'redis_prefix' => env('SEED_COUNTRY_REDIS_PREFIX', 'fleet_' . strtolower(env('SEED_COUNTRY_CODE', 'sy')) . ':'),
                'is_active' => true,
            ]
        );
    }

    private function primaryDbConfig(): array
    {
        $global = config('database.connections.global');

        if (is_array($global) && ! empty($global['database'])) {
            return $global;
        }

        $default = config('database.default');

        return config('database.connections.' . $default, []);
    }
}
