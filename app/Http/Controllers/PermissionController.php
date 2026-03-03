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
    public function index(Request $request)
    {
        $permissions = Permission::query()
            ->when($request->search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->paginate(10);

        return Inertia::render('Permissions/Index', [
            'permissions' => $permissions,
            'filters' => $request->only(['search']),
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
            'guard_name' => 'sometimes|string'
        ]);

        Permission::create([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? 'web'
        ]);

        return redirect()->route('permissions.index')
            ->with('success', 'Permission created successfully.');
    }

    /**
     * Update the specified permission.
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|unique:permissions,name,' . $permission->id,
            'guard_name' => 'sometimes|string'
        ]);

        $permission->update([
            'name' => $request->name,
            'guard_name' => $request->guard_name ?? $permission->guard_name
        ]);

        return redirect()->route('permissions.index')
            ->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified permission.
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->route('permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }

    /**
     * Assign permission to role.
     */
    public function assignToRole(Request $request, Permission $permission)
    {
        $request->validate([
            'role' => 'required|exists:roles,name'
        ]);

        $role = Role::findByName($request->role);
        $role->givePermissionTo($permission);

        return back()->with('success', 'Permission assigned to role successfully.');
    }

    /**
     * Remove permission from role.
     */
    public function removeFromRole(Request $request, Permission $permission)
    {
        $request->validate([
            'role' => 'required|exists:roles,name'
        ]);

        $role = Role::findByName($request->role);
        $role->revokePermissionTo($permission);

        return back()->with('success', 'Permission removed from role successfully.');
    }
}
