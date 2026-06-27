<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class RoleController extends Controller
{
    /**
     * Display a listing of roles.
     */
    public function index(): InertiaResponse
    {
        $roles = Role::withCount(['permissions', 'users'])->get();
        
        return Inertia::render('Roles/Index', [
            'roles' => $roles,
        ]);
    }

    /**
     * Show the form for editing the specified role.
     */
    public function edit(Role $role): InertiaResponse
    {
        $role->load('permissions');
        $permissions = Permission::orderBy('name')->get();
        
        // Group permissions by module
        $groupedPermissions = $permissions->groupBy(function ($permission) {
            $parts = explode('-', $permission->name);
            // Remove action prefix (view, create, edit, delete, manage)
            if (count($parts) > 1) {
                array_shift($parts);
            }
            return ucfirst(implode(' ', $parts));
        });
        
        return Inertia::render('Roles/Edit', [
            'role' => $role,
            'groupedPermissions' => $groupedPermissions,
        ]);
    }

    /**
     * Update the specified role in storage.
     */
    public function update(RoleRequest $request, Role $role): RedirectResponse
    {
        $role->update([
            'name' => $request->name,
        ]);

        // Sync permissions
        $role->permissions()->sync($request->permissions ?? []);

        return redirect()->route('roles.index')
            ->with('success', 'Role berhasil diperbarui');
    }

    /**
     * Remove the specified role from storage.
     */
    public function destroy(Role $role): RedirectResponse
    {
        // Prevent deleting system roles
        $systemRoles = ['super-admin', 'owner', 'operator', 'customer', 'partner'];
        if (in_array($role->name, $systemRoles)) {
            return redirect()->back()
                ->with('error', 'Role sistem tidak dapat dihapus');
        }

        // Check if role has users
        if ($role->users()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Role masih digunakan oleh user');
        }

        $role->delete();

        return redirect()->route('roles.index')
            ->with('success', 'Role berhasil dihapus');
    }
}
