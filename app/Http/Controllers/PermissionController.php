<?php
namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\ParentPermission;
use App\Models\Role;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::with('parent')->get()->unique('name')->values();
        return response()->json($permissions);
    }

    public function show($id)
    {
        $permission = Permission::with('parent')->findOrFail($id);
        return response()->json($permission);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
            'guard_name' => 'required|string',
            'parent_id' => 'nullable|exists:parent_permissions,id',
        ]);

        $permission = Permission::create($request->only('name', 'guard_name', 'parent_id'));
        return response()->json($permission, 201);
    }


    public function getRoles()
    {
        $roles = Role::all();
        return response()->json($roles);
    }

    public function getRolePermissions($roleId)
    {
        $role = Role::findOrFail($roleId);
        $permissions = $role->permissions()->get(['name']);
        return response()->json($permissions);
    }

    public function assignPermissionToRole(Request $request, $roleId)
    {
        $request->validate([
            'permission_name' => 'required|string|exists:permissions,name',
        ]);

        $role = Role::findOrFail($roleId);
        $permissionName = $request->input('permission_name');

        if(!$role->hasPermissionTo($permissionName)) {
            $role->givePermissionTo($permissionName);
        }

        return response()->json(['message' => 'تم تعيين الصلاحية بنجاح']);
    }

    public function removePermissionFromRole(Request $request, $roleId)
    {
        $request->validate([
            'permission_name' => 'required|string|exists:permissions,name',
        ]);

        $role = Role::findOrFail($roleId);
        $permissionName = $request->input('permission_name');

        if($role->hasPermissionTo($permissionName)) {
            $role->revokePermissionTo($permissionName);
        }

        return response()->json(['message' => 'تم إزالة الصلاحية بنجاح']);
    }
}
