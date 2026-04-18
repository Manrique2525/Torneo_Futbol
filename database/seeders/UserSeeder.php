<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;


class UserSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── 1. Crear tenant GLOBAL (sistema)
        $tenant = Tenant::firstOrCreate([
            'slug' => 'system',
        ], [
            'uuid'     => (string) Str::uuid(),
            'name'     => 'Sistema',
            'email'    => 'system@admin.com',
            'country'  => 'MX',
            'timezone' => 'America/Mexico_City',
            'plan'     => 'internal',
            'status'   => Tenant::STATUS_ACTIVE,
        ]);

        // 🔴 CONTEXTO DEL TENANT (OBLIGATORIO)
        app(\Spatie\Permission\PermissionRegistrar::class)
    ->setPermissionsTeamId($tenant->id);

        // ── 2. Crear rol super_admin
        $role = Role::firstOrCreate([
            'name'       => RoleEnum::SUPER_ADMIN,
            'guard_name' => 'web',
            'tenant_id'  => $tenant->id,
        ]);

        // (Opcional pero recomendado)
        // darle TODOS los permisos
        $role->syncPermissions(\App\Enums\PermissionEnum::all());

        // ── 3. Crear usuario super admin
        $user = User::withoutGlobalScopes()->firstOrCreate([
            'email' => 'superadmin@demo.com',
        ], [
            'tenant_id' => $tenant->id,
            'name'      => 'Super Admin',
            'password'  => Hash::make('password'),
            'status'    => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        // ── 4. Asignar rol
        $user->assignRole(RoleEnum::SUPER_ADMIN);

        // ── Output
        $this->command->info('');
        $this->command->info('🧠 Super Admin creado correctamente');
        $this->command->info('Tenant: ' . $tenant->name);

        $this->command->table(
            ['Email', 'Password', 'Role'],
            [
                ['superadmin@demo.com', 'password', 'super_admin'],
            ]
        );
    }
}
