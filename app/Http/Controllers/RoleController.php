<?php

namespace App\Http\Controllers;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('perms.parent')->get();
        return response()->json($roles);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:roles,name',
            'guard_name' => 'required|string',
            'status' => 'nullable|integer',
        ]);

        $role = Role::create($request->only('name', 'guard_name', 'status'));
        return response()->json($role, 201);
    }

    public function show($id)
    {
        $role = Role::with('perms.parent')->findOrFail($id);
        return response()->json($role);
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => [
                'required',
                'string',
                Rule::unique('roles')->ignore($role->id),
            ],
            'guard_name' => 'required|string',
            'status' => 'nullable|integer',
        ]);

        $role->update($request->only('name', 'guard_name', 'status'));

        return response()->json($role);
    }

    public function destroy($id)
    {
        $role = Role::findOrFail($id);

        $role->perms()->detach();

        $role->delete();

        return response()->json(['message' => 'تم حذف الرول بنجاح']);
    }

    public function assignPermissions(Request $request, $id)
    {
        $role = Role::findOrFail($id);
    
        $request->validate([
            'permissions' => 'required|array',
            'permissions.*' => 'string|exists:permissions,name',  
        ]);
    
        $permissionIds = Permission::whereIn('name', $request->permissions)->pluck('id')->toArray();
    
        $role->perms()->sync($permissionIds);
    
        return response()->json(['message' => 'تم تحديث الصلاحيات بنجاح']);
    }

    public function assignPermission(Request $request, $id) {
        $request->validate(['permission' => 'required|string|exists:permissions,name']);
        $role = Role::findOrFail($id);
        $role->givePermissionTo($request->permission);
        return response()->json(['message' => 'تم إسناد الصلاحية']);
    }
    
    public function removePermission(Request $request, $id) {
        $request->validate(['permission' => 'required|string|exists:permissions,name']);
        $role = Role::findOrFail($id);
        $role->revokePermissionTo($request->permission);
        return response()->json(['message' => 'تم إزالة الصلاحية']);
    }
    
    
}
