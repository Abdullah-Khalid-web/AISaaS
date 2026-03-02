<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    /**
     * Display a listing of permissions.
     */
    public function index()
    {
        $permissions = Permission::with('roles')->get();

        return Inertia::render('Permissions/Index', [
            'permissions' => $permissions,
            'roles' => Role::all(),
        ]);
    }

    /**
     * Store a newly created permission.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
            'roles' => 'array',
        ]);

        $permission = Permission::create(['name' => $request->name]);

        if ($request->has('roles')) {
            $permission->syncRoles($request->roles);
        }

        return back()->with('success', 'Permission created successfully.');
    }

    /**
     * Update the specified permission.
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
            'roles' => 'array',
        ]);

        $permission->update(['name' => $request->name]);

        if ($request->has('roles')) {
            $permission->syncRoles($request->roles);
        }

        return back()->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified permission.
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return back()->with('success', 'Permission deleted successfully.');
    }

    /**
     * Assign permission to role.
     */
    public function assignToRole(Request $request, Permission $permission)
    {
        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        $permission->assignRole($request->role);

        return back()->with('success', 'Permission assigned to role successfully.');
    }

    /**
     * Remove permission from role.
     */
    public function removeFromRole(Request $request, Permission $permission)
    {
        $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        $permission->removeRole($request->role);

        return back()->with('success', 'Permission removed from role successfully.');
    }
}
