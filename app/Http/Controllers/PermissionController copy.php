<?php
namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\ParentPermission;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::with('parent')->get()->unique('name')->values();
        return response()->json($permissions);
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

    public function show($id)
    {
        $permission = Permission::with('parent')->findOrFail($id);
        return response()->json($permission);
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
            'guard_name' => 'required|string',
            'parent_id' => 'nullable|exists:parent_permissions,id',
        ]);

        $permission->update($request->only('name', 'guard_name', 'parent_id'));

        return response()->json($permission);
    }

    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);
        $permission->roles()->detach();
        $permission->delete();

        return response()->json(['message' => 'Permission deleted successfully']);
    }
}
