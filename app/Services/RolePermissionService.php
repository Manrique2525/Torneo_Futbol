<?php

namespace App\Services;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionService
{
    /**
     * Create default roles for a new tenant.
     * Called when a tenant is first created.
     */
    public function setupTenantRoles(int $tenantId): void
    {
        // Set Spatie context to this tenant
        setPermissionsTeamId($tenantId);

        foreach (RoleEnum::assignable() as $roleEnum) {
            $role = Role::findOrCreate($roleEnum->value, 'web');
            $role->syncPermissions($roleEnum->defaultPermissions());
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Get all roles for the current tenant.
     */
    public function getTenantRoles(): Collection
    {
        return Role::where('guard_name', 'web')
            ->where(function ($query) {
                $query->where('tenant_id', getPermissionsTeamId())
                      ->orWhereNull('tenant_id'); // include global roles
            })
            ->with('permissions:id,name')
            ->get()
            ->map(fn (Role $role) => [
                'id'          => $role->id,
                'name'        => $role->name,
                'is_global'   => is_null($role->tenant_id),
                'permissions' => $role->permissions->pluck('name'),
            ]);
    }

    /**
     * Get all permissions grouped by module (for the admin UI).
     */
    public function getPermissionsGrouped(): array
    {
        return PermissionEnum::grouped();
    }

    /**
     * Create a custom role for the current tenant.
     */
    public function createRole(string $name, array $permissionNames): Role
    {
        // Validate permission names
        $valid = PermissionEnum::all();
        $filtered = array_intersect($permissionNames, $valid);

        $role = Role::create([
            'name'       => $name,
            'guard_name' => 'web',
            // tenant_id is set automatically by Spatie when teams=true
        ]);

        $role->syncPermissions($filtered);

        return $role;
    }

    /**
     * Update permissions on an existing role.
     * Cannot modify global roles — only tenant-scoped ones.
     */
    public function updateRolePermissions(Role $role, array $permissionNames): Role
    {
        if (is_null($role->tenant_id)) {
            abort(403, 'Cannot modify global roles. Create a custom role instead.');
        }

        $valid = PermissionEnum::all();
        $filtered = array_intersect($permissionNames, $valid);

        $role->syncPermissions($filtered);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return $role->load('permissions');
    }

    /**
     * Delete a custom role (not global).
     */
    public function deleteRole(Role $role): void
    {
        if (is_null($role->tenant_id)) {
            abort(403, 'Cannot delete global roles.');
        }

        // Don't allow deleting default role names
        $protectedNames = array_map(fn ($r) => $r->value, RoleEnum::assignable());
        if (in_array($role->name, $protectedNames)) {
            abort(403, 'Cannot delete default roles. You may modify their permissions instead.');
        }

        $role->delete();
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Assign a role to a user within the current tenant.
     */
    public function assignRoleToUser(User $user, string $roleName): void
    {
        if ($roleName === RoleEnum::SUPER_ADMIN->value) {
            abort(403, 'Cannot assign super_admin role from tenant context.');
        }

        $user->assignRole($roleName);
    }

    /**
     * Remove a role from a user.
     */
    public function removeRoleFromUser(User $user, string $roleName): void
    {
        $user->removeRole($roleName);
    }

    /**
     * Give a direct permission to a user (overrides role permissions).
     * Use case: tenant admin wants to give ONE extra permission to a user.
     */
    public function giveDirectPermission(User $user, string $permissionName): void
    {
        if (!in_array($permissionName, PermissionEnum::all())) {
            abort(422, "Invalid permission: {$permissionName}");
        }

        $user->givePermissionTo($permissionName);
    }

    /**
     * Revoke a direct permission from a user.
     * Use case: tenant admin wants to REMOVE a specific permission
     * that the user would normally get from their role.
     */
    public function revokeDirectPermission(User $user, string $permissionName): void
    {
        $user->revokePermissionTo($permissionName);
    }

    /**
     * Get a detailed view of a user's permissions.
     * Shows which come from roles and which are direct.
     */
    public function getUserPermissions(User $user): array
    {
        return [
            'roles'              => $user->getRoleNames(),
            'permissions_via_roles' => $user->getPermissionsViaRoles()->pluck('name')->unique(),
            'direct_permissions' => $user->getDirectPermissions()->pluck('name'),
            'all_permissions'    => $user->getAllPermissions()->pluck('name')->unique(),
        ];
    }
}
