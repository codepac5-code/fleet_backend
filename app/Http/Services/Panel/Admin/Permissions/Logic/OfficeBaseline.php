<?php

namespace App\Http\Services\Panel\Admin\Permissions\Logic;

use App\Http\Core\Const\Options\Guard;
use App\Models\Office;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Facades\Schema;

/**
 * What a brand-new office is allowed to do on day one.
 *
 * Nothing used to grant an office anything: not the admin's "add office" form,
 * not lead approval, not the website's self-signup. Permissions arrived only if
 * somebody later opened the permission matrix and ticked boxes by hand, so an
 * office signed in to a panel where half the screens were missing and the other
 * half answered 403 on save — the corridors page would load (it needs `view
 * sub-service list`) and then refuse to save (it needs `edit sub-service`).
 *
 * The platform already states what an office may do: the `Office` role seeded
 * by RolePermissionSeeder. Its permissions are copied onto the office DIRECTLY
 * rather than assigning the role, because the permission matrix screen edits
 * direct permissions only — a role's grants would show there as ticked and
 * un-untickable.
 */
class OfficeBaseline
{
    /**
     * Kept in step with the `Office` role in RolePermissionSeeder. Used when a
     * shard has no seeded role — a country provisioned before the seeder ran
     * would otherwise hand its offices an empty panel.
     */
    private const FALLBACK = [
        'view dashboard',
        'order history',
        'follow orders',
        'monthly revenue',
        'track drivers locations',
        'booking list',
        'edit order status',
        'show order details',
        'add vehicle', 'delete vehicle', 'update vehicle', 'view vehicle list',
        'add employee', 'delete employee', 'update employee', 'view employee list',
        'add driver', 'delete driver', 'edit driver', 'view driver list',
        'assign permissions',
        'view service list',
        'view sub-service list', 'edit sub-service',
        // An office sets what it takes from its own drivers, so it needs to
        // edit commission and not only read it.
        'view commission', 'edit commission',
        'view tickets',
    ];

    public function __construct(private PermissionMatrix $matrix)
    {
    }

    public function grant(Office $office): void
    {
        $names = $this->names($office->getConnectionName());

        if ($names === []) {
            return;
        }

        $this->matrix->sync($office, $names, Guard::$Office);
    }

    public function names(?string $connection): array
    {
        // A shard without the permission tables (a bare test fixture, a country
        // mid-provision) grants nothing rather than failing the creation — a
        // missing baseline is a smaller failure than no office at all.
        if (! Schema::connection($connection)->hasTable('permissions')) {
            return [];
        }

        $role = Schema::connection($connection)->hasTable('roles')
            ? Role::on($connection)
                ->where('name', 'Office')
                ->where('guard_name', Guard::$Office)
                ->first()
            : null;

        $names = $role !== null
            ? $role->permissions()->pluck('name')->all()
            : [];

        if ($names === []) {
            $names = self::FALLBACK;
        }

        // Only names the shard actually knows: a permission that does not exist
        // here cannot be granted, and syncing it would drop it silently anyway.
        return Permission::on($connection)
            ->where('guard_name', Guard::$Office)
            ->whereIn('name', $names)
            ->pluck('name')
            ->all();
    }
}
