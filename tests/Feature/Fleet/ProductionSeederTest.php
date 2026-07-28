<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Const\Billing\BillingMode;
use App\Http\Services\Panel\Shared\Authorization\PanelPermission;
use App\Models\Admin;
use App\Models\Currency;
use App\Models\InfrastructureNode;
use App\Models\SiteSetting;
use App\Models\SubscriptionPlan;
use Database\Seeders\ProductionSeeder;
use Spatie\Permission\Models\Permission;

class ProductionSeederTest extends FleetTestCase
{
    protected array $globalMigrations = [
        '2026_06_19_000002_create_currencies_table.php',
        '2026_06_25_000001_create_subscription_plans_table.php',
        '2026_07_01_000005_add_is_popular_to_subscription_plans.php',
        '2026_07_13_000005_add_trial_days_to_subscription_plans.php',
        '2026_07_01_000006_create_site_settings_table.php',
        '2026_05_29_220120_create_infrastructure_nodes_table.php',
        '2026_06_25_000011_add_currency_to_infrastructure_nodes.php',
        '2026_07_13_000004_add_billing_mode_to_infrastructure_nodes.php',
    ];

    protected array $tenantMigrations = [
        '2025_06_20_134508_create_parent_permissions_table.php',
        '2024_11_03_151720_create_permission_tables.php',
        '2024_11_05_124211_create_admins_table.php',
        '2026_06_19_000001_add_remember_token_to_admins_table.php',
    ];

    public function test_production_seeder_bootstraps_all_defaults(): void
    {
        $this->seed(ProductionSeeder::class);

        $admin = Admin::query()->where('email', 'admin@fleetos.app')->first();
        $this->assertNotNull($admin);
        $this->assertTrue($admin->roles()->where('name', 'super-admin')->exists());

        $this->assertTrue(Permission::query()->where('name', PanelPermission::VIEW_SETTINGS)->where('guard_name', 'admin')->exists());
        $this->assertTrue(Permission::query()->where('name', PanelPermission::MANAGE_CURRENCIES)->where('guard_name', 'admin')->exists());

        $usd = Currency::query()->where('code', 'USD')->first();
        $this->assertNotNull($usd);
        $this->assertTrue((bool) $usd->is_default);

        $this->assertSame(5, SubscriptionPlan::query()->count());
        $business = SubscriptionPlan::query()->where('key', 'business')->first();
        $this->assertTrue((bool) $business->is_popular);
        $this->assertSame(14, (int) $business->trial_days);
        $this->assertNull(SubscriptionPlan::query()->where('key', 'free')->first()->trial_days);

        $this->assertSame('120', (string) SiteSetting::val('otp_ttl_seconds'));

        $node = InfrastructureNode::query()->where('type', 'country')->first();
        $this->assertNotNull($node);
        $this->assertTrue((bool) $node->is_active);
        $this->assertSame(BillingMode::COMMISSION, $node->billing_mode);
    }

    public function test_production_seeder_is_idempotent(): void
    {
        $this->seed(ProductionSeeder::class);
        $this->seed(ProductionSeeder::class);

        $this->assertSame(1, Admin::query()->where('email', 'admin@fleetos.app')->count());
        $this->assertSame(5, SubscriptionPlan::query()->count());
        $this->assertSame(1, Currency::query()->where('code', 'USD')->count());
        $this->assertSame(1, InfrastructureNode::query()->where('type', 'country')->count());
    }
}
