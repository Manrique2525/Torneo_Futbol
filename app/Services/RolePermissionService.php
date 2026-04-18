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
     * Setup roles (ONLY once globally, not per tenant)
     */
    public function setupRoles(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions
        foreach (PermissionEnum::all() as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        // Roles
        foreach (RoleEnum::assignable() as $roleName) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions(RoleEnum::defaultPermissions($roleName));
        }

        // Super admin
        Role::findOrCreate(RoleEnum::SUPER_ADMIN, 'web');
    }

    /**
     * Get all roles (global)
     */
    public function getRoles(): Collection
    {
        return Role::with('permissions:id,name')
            ->get()
            ->map(fn (Role $role) => [
                'id'          => $role->id,
                'name'        => $role->name,
                'permissions' => $role->permissions->pluck('name'),
            ]);
    }

    /**
     * Get permissions grouped
     */
public function getPermissionsGrouped(): array
{
    return Permission::query()
        ->select('name')
        ->get()
        ->pluck('name')
        ->groupBy(function ($permission) {
            return explode('.', $permission)[0]; // módulo
        })
        ->map(function ($permissions, $module) {
            return $permissions->values();
        })
        ->toArray();
}

    /**
     * Create custom role
     */
    public function createRole(string $name, array $permissionNames): Role
    {
        $valid = PermissionEnum::all();
        $filtered = array_intersect($permissionNames, $valid);

        $role = Role::create([
            'name'       => $name,
            'guard_name' => 'web',
        ]);

        $role->syncPermissions($filtered);

        return $role;
    }

    /**
     * Update role permissions
     */
    public function updateRolePermissions(Role $role, array $permissionNames): Role
    {
        if ($role->name === RoleEnum::SUPER_ADMIN) {
            abort(403, 'Cannot modify super admin.');
        }

        $valid = PermissionEnum::all();
        $filtered = array_intersect($permissionNames, $valid);

        $role->syncPermissions($filtered);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return $role->load('permissions');
    }

    /**
     * Delete role
     */
    public function deleteRole(Role $role): void
    {
        if ($role->name === RoleEnum::SUPER_ADMIN) {
            abort(403, 'Cannot delete super admin.');
        }

        $role->delete();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Assign role to user (scoped by team automatically)
     */
    public function assignRoleToUser(User $user, string $roleName): void
    {
        if ($roleName === RoleEnum::SUPER_ADMIN) {
            abort(403, 'Cannot assign super_admin from tenant context.');
        }

        $user->assignRole($roleName);
    }

    /**
     * Remove role
     */
    public function removeRoleFromUser(User $user, string $roleName): void
    {
        $user->removeRole($roleName);
    }

    /**
     * Direct permission
     */
    public function giveDirectPermission(User $user, string $permissionName): void
    {
        if (!in_array($permissionName, PermissionEnum::all())) {
            abort(422, "Invalid permission: {$permissionName}");
        }

        $user->givePermissionTo($permissionName);
    }

    public function revokeDirectPermission(User $user, string $permissionName): void
    {
        $user->revokePermissionTo($permissionName);
    }

    /**
     * User permissions detail
     */
    public function getUserPermissions(User $user): array
    {
        return [
            'roles' => $user->getRoleNames(),
            'permissions_via_roles' => $user->getPermissionsViaRoles()->pluck('name')->unique(),
            'direct_permissions' => $user->getDirectPermissions()->pluck('name'),
            'all_permissions' => $user->getAllPermissions()->pluck('name')->unique(),
        ];
    }
}
