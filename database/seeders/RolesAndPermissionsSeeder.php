<?php

namespace Database\Seeders;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── 1. Create all permissions (global, no team) ──
        $permissions = PermissionEnum::all();
        foreach ($permissions as $permissionName) {
            Permission::findOrCreate($permissionName, 'web');
        }

        $this->command->info(' Created ' . count($permissions) . ' permissions.');

        // ── 2. Create global roles and assign default permissions ──
        // Global roles (team_id = null) serve as TEMPLATES.
        // When a tenant is created, these are copied for that tenant.
        foreach (RoleEnum::assignable() as $roleEnum) {
            $role = Role::findOrCreate($roleEnum->value, 'web');

            $rolePermissions = $roleEnum->defaultPermissions();
            $role->syncPermissions($rolePermissions);

            $this->command->info(" Role [{$roleEnum->value}] → " . count($rolePermissions) . ' permissions');
        }

        // Super admin role (no permissions needed — Gate::before handles it)
        Role::findOrCreate(RoleEnum::SUPER_ADMIN->value, 'web');
        $this->command->info(' Role [super_admin] → bypasses all via Gate::before');
    }
}
