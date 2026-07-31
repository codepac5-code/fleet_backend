<?php

namespace Tests\Feature\Fleet;

use App\Http\Core\Const\Options\Guard;
use App\Http\Services\Panel\Admin\Offices\Logic\OfficeRepository;
use App\Http\Services\Panel\Admin\Permissions\Logic\OfficeBaseline;
use App\Models\Permission;
use Illuminate\Support\Facades\DB;

/**
 * A new office can use the panel it just signed in to.
 *
 * Nothing granted an office anything: not the admin's "add office" form, not
 * lead approval, not the website's self-signup. Permissions arrived only if
 * somebody later opened the permission matrix by hand, so offices existed with
 * NO permissions at all, and one held `view sub-service list` without `edit
 * sub-service` — it could open the fixed-corridors page and then got
 * "403 User does not have the right permissions" the moment it saved a price.
 */
class OfficeBaselinePermissionsTest extends FleetTestCase
{
    protected array $tenantMigrations = [
        '2024_10_29_211028_create_offices_table.php',
        '2025_06_21_103445_create_office_services_table.php',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        DB::connection('fleet_test')->statement('PRAGMA foreign_keys = OFF');

        $this->runMigration('2024_11_03_151720_create_permission_tables.php');

        foreach (['view dashboard', 'view sub-service list', 'edit sub-service', 'view commission', 'view driver list'] as $name) {
            Permission::query()->create(['name' => $name, 'guard_name' => Guard::$Office]);
        }
    }

    private function create(): \App\Models\Office
    {
        return app(OfficeRepository::class)->create([
            'officeName' => 'Damascus Luxury Fleet',
            'email' => 'damascusluxury@fleet.plus',
            'password' => 'secret123',
            'status' => 1,
        ]);
    }

    public function test_a_new_office_is_created_with_the_baseline_permissions(): void
    {
        $office = $this->create();

        $held = $office->getAllPermissions()->pluck('name');

        $this->assertContains('view dashboard', $held);
        $this->assertContains('view driver list', $held);
    }

    public function test_it_can_both_open_and_save_its_pricing(): void
    {
        // Viewing without editing is the exact shape of the live 403: the
        // corridors page loads, then the save is refused.
        $held = $this->create()->getAllPermissions()->pluck('name');

        $this->assertContains('view sub-service list', $held);
        $this->assertContains('edit sub-service', $held, 'an office that can open the pricing page must be able to save it');
    }

    public function test_it_can_see_its_own_money_and_subscription(): void
    {
        $this->assertContains('view commission', $this->create()->getAllPermissions()->pluck('name'));
    }

    public function test_the_baseline_only_offers_permissions_the_shard_actually_has(): void
    {
        // The fixture defines five of the baseline's names; a permission that
        // does not exist here must not be offered, because syncing it would
        // drop it silently and the grant would read back short.
        $names = app(OfficeBaseline::class)->names(null);

        sort($names);

        $this->assertSame(
            ['edit sub-service', 'view commission', 'view dashboard', 'view driver list', 'view sub-service list'],
            $names,
        );
    }
}
