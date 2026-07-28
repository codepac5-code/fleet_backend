<?php

namespace App\Http\Services\Panel\Admin\Permissions\Logic;

use App\Models\ParentPermission;
use Illuminate\Support\Facades\App;

class PermissionMatrix
{
    private const GROUP_ORDER = [
        'dashboard', 'office', 'employee', 'driver', 'user', 'vehicle',
        'service', 'sub-service', 'orders', 'system', 'department', 'banners', 'issues',
    ];

    private function order(string $name): int
    {
        $index = array_search($name, self::GROUP_ORDER, true);

        return $index === false ? count(self::GROUP_ORDER) : $index;
    }

    public function groups(string $guard): array
    {
        return ParentPermission::where('guard_name', $guard)
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
        return ParentPermission::where('guard_name', $guard)
            ->with(['permissions' => fn ($q) => $q->where('guard_name', $guard)])
            ->get()
            ->flatMap(fn ($parent) => $parent->permissions->pluck('name'))
            ->unique()
            ->values()
            ->all();
    }

    public function sync($model, array $names, string $guard, ?string $baseRole = null): void
    {
        $valid = array_values(array_intersect($this->validNames($guard), $names));

        if ($baseRole && $model->hasRole($baseRole)) {
            $model->removeRole($baseRole);
        }

        $model->syncPermissions($valid);

        App::make(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
