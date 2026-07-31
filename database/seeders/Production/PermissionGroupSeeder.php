<?php

namespace Database\Seeders\Production;

use App\Http\Services\Panel\Shared\Authorization\PanelPermission as Perm;
use App\Models\ParentPermission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Throwable;

/**
 * Puts every permission in a group.
 *
 * The permissions matrix renders GROUPS (`parent_permissions`), and
 * `PermissionMatrix::sync()` only keeps names it finds through them — so a
 * permission with no `parent_id` is invisible in the UI *and silently dropped*
 * when saving. Eleven of them were in exactly that state (booking list, view
 * tickets, view commission, view payments, the rating lists…), which made them
 * ungrantable to any employee. This maps every PanelPermission constant to its
 * group and backfills the link, per guard. Idempotent.
 */
class PermissionGroupSeeder extends Seeder
{
    private const GUARDS = ['admin', 'office', 'employee'];

    private const GROUPS = [
        'dashboard' => [Perm::VIEW_DASHBOARD],
        'office' => [Perm::VIEW_OFFICE_LIST, Perm::ADD_OFFICE, Perm::UPDATE_OFFICE, Perm::DELETE_OFFICE, 'edit office', 'office overview', 'office change custom commission'],
        'employee' => [Perm::VIEW_EMPLOYEE_LIST, Perm::ADD_EMPLOYEE, Perm::UPDATE_EMPLOYEE, Perm::DELETE_EMPLOYEE, Perm::ASSIGN_PERMISSIONS],
        'driver' => [Perm::VIEW_DRIVER_LIST, Perm::ADD_DRIVER, Perm::EDIT_DRIVER, Perm::DELETE_DRIVER, Perm::VIEW_DRIVER_RATINGS],
        'user' => [Perm::VIEW_USER_LIST, Perm::ADD_USER, Perm::EDIT_USER, Perm::DELETE_USER, Perm::VIEW_USER_RATINGS],
        'vehicle' => [Perm::VIEW_VEHICLE_LIST, Perm::ADD_VEHICLE, Perm::UPDATE_VEHICLE, Perm::DELETE_VEHICLE],
        'service' => [Perm::VIEW_SERVICE_LIST, Perm::ADD_SERVICE, Perm::EDIT_SERVICE, Perm::DELETE_SERVICE],
        'sub-service' => [Perm::VIEW_SUB_SERVICE_LIST, Perm::ADD_SUB_SERVICE, Perm::EDIT_SUB_SERVICE, Perm::DELETE_SUB_SERVICE],
        'orders' => [Perm::VIEW_BOOKING_LIST, Perm::ORDER_HISTORY, Perm::SHOW_ORDER_DETAILS, Perm::EDIT_ORDER_STATUS],
        'system' => [
            Perm::VIEW_COMMISSION, Perm::EDIT_COMMISSION, Perm::VIEW_PAYMENTS, Perm::VIEW_WALLET_TRANSACTIONS,
            Perm::MANAGE_ROLES_PERMISSIONS, Perm::MANAGE_CURRENCIES, Perm::VIEW_SETTINGS, Perm::VIEW_COUPON_LIST,
        ],
        'department' => [Perm::VIEW_DEPARTMENTS],
        'banners' => [Perm::VIEW_BANNER_LIST],
        'issues' => [Perm::VIEW_TICKETS],
    ];

    public function run(): void
    {
        if (! Schema::hasTable('parent_permissions') || ! Schema::hasTable('permissions')) {
            return;
        }

        foreach (self::GUARDS as $guard) {
            foreach (self::GROUPS as $group => $names) {
                try {
                    $parent = ParentPermission::query()->firstOrCreate(['name' => $group, 'guard_name' => $guard]);

                    Permission::query()
                        ->where('guard_name', $guard)
                        ->whereIn('name', $names)
                        ->whereNull('parent_id')
                        ->update(['parent_id' => $parent->id]);
                } catch (Throwable $e) {
                    continue;
                }
            }
        }
    }
}
