<?php

namespace App\Http\Services\Panel\Admin\Permissions\Logic;

use App\Models\ParentPermission;
use App\Models\Permission;
use Illuminate\Support\Facades\App;

class PermissionMatrix
{
    private const GROUP_ORDER = [
        'dashboard', 'office', 'employee', 'driver', 'user', 'vehicle',
        'service', 'sub-service', 'orders', 'system', 'department', 'banners', 'issues', 'other',
    ];

    private function order(string $name): int
    {
        $index = array_search($name, self::GROUP_ORDER, true);

        return $index === false ? count(self::GROUP_ORDER) : $index;
    }

    public function groups(string $guard): array
    {
        $groups = ParentPermission::where('guard_name', $guard)
            ->with(['permissions' => fn ($q) => $q->where('guard_name', $guard)->orderBy('name')])
            ->get()
            ->filter(fn ($parent) => $parent->permissions->isNotEmpty())
            ->sortBy(fn ($parent) => $this->order($parent->name))
            ->map(fn ($parent) => [
                'key'         => $parent->name,
                'label'       => PermissionLabels::group($parent->name),
                'permissions' => $parent->permissions->map(fn ($permission) => [
                    'name'  => $permission->name,
                    'label' => PermissionLabels::permission($permission->name),
                ])->values()->all(),
            ])
            ->values()
            ->all();

        // Any permission that isn't under a rendered group would otherwise be
        // INVISIBLE on the page and silently dropped on save (sync() keeps only
        // names it finds). Surface every leftover in an "Other" group so the page
        // truly lists ALL permissions and none is quietly ungrantable.
        $shown = collect($groups)->flatMap(fn ($g) => array_column($g['permissions'], 'name'))->all();

        $ungrouped = Permission::where('guard_name', $guard)
            ->when($shown !== [], fn ($q) => $q->whereNotIn('name', $shown))
            ->orderBy('name')
            ->pluck('name');

        if ($ungrouped->isNotEmpty()) {
            $groups[] = [
                'key'         => 'other',
                'label'       => PermissionLabels::group('other'),
                'permissions' => $ungrouped->map(fn ($name) => [
                    'name'  => $name,
                    'label' => PermissionLabels::permission($name),
                ])->values()->all(),
            ];
        }

        return $groups;
    }

    public function granted($model, string $guard): array
    {
        return $model->getAllPermissions()
            ->where('guard_name', $guard)
            ->pluck('name')
            ->all();
    }

    public function validNames(string $guard): array
    {
        // Every permission of the guard is grantable — including any not yet
        // filed under a group — so the "Other" group on the page can actually be
        // saved rather than silently discarded.
        return Permission::where('guard_name', $guard)
            ->pluck('name')
            ->unique()
            ->values()
            ->all();
    }

    public function sync($model, array $names, string $guard, ?string $baseRole = null): void
    {
        $valid = array_values(array_intersect($this->validNames($guard), $names));

        // Resolve the role/permission MODELS on the SAME connection as the owner
        // (office/employee live on a per-country shard; admin on global). Spatie's
        // string lookups (`findByName`) instead use the default connection, so a
        // shard owner would get GLOBAL permission ids written into its SHARD pivot
        // — ids that don't exist there — and the grant would read back empty.
        // Passing instances loaded on the owner's connection keeps the ids and the
        // pivot on one database, so per-shard permissions actually persist.
        $conn = $model->getConnectionName();

        if ($baseRole) {
            $role = \App\Models\Role::on($conn)->where('name', $baseRole)->where('guard_name', $guard)->first();

            if ($role && $model->hasRole($role)) {
                $model->removeRole($role);
            }
        }

        $permissions = \App\Models\Permission::on($conn)
            ->where('guard_name', $guard)
            ->whereIn('name', $valid)
            ->get();

        $model->syncPermissions($permissions);

        App::make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
