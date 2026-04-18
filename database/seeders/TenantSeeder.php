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

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ── 1. Crear tenant ──────────────────────────
        $tenant = Tenant::create([
            'uuid'     => (string) Str::uuid(),
            'name'     => 'Liga Municipal Villahermosa',
            'slug'     => 'liga-villahermosa',
            'email'    => 'admin@ligavillahermosa.com',
            'phone'    => '9931234567',
            'country'  => 'MX',
            'timezone' => 'America/Mexico_City',
            'plan'     => 'pro',
            'status'   => Tenant::STATUS_ACTIVE,
        ]);

        //  MUY IMPORTANTE: establecer contexto del tenant
        setPermissionsTeamId($tenant->id);

        // ── 2. Crear roles para ESTE tenant ──────────
        foreach (RoleEnum::assignable() as $roleName) {

            $role = Role::firstOrCreate([
                'name'       => $roleName,
                'guard_name' => 'web',
                'tenant_id'  => $tenant->id,
            ]);

            $role->syncPermissions(
                RoleEnum::defaultPermissions($roleName)
            );
        }

        // ── 3. Crear usuarios ────────────────────────
        $admin = User::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name'      => 'Admin Liga',
            'email'     => 'admin@demo.com',
            'password'  => Hash::make('password'),
            'phone'     => '9931000001',
            'status'    => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $manager = User::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name'      => 'Carlos Coordinador',
            'email'     => 'manager@demo.com',
            'password'  => Hash::make('password'),
            'status'    => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $referee = User::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name'      => 'Roberto Árbitro',
            'email'     => 'referee@demo.com',
            'password'  => Hash::make('password'),
            'status'    => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        $delegate = User::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name'      => 'Miguel Delegado',
            'email'     => 'delegate@demo.com',
            'password'  => Hash::make('password'),
            'status'    => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);

        // ── 4. Asignar roles (YA EN CONTEXTO) ────────
        $admin->assignRole(RoleEnum::ADMIN);
        $manager->assignRole(RoleEnum::MANAGER);
        $referee->assignRole(RoleEnum::REFEREE);
        $delegate->assignRole(RoleEnum::DELEGATE);

        // ── Summary ─────────────────────────────────
        $this->command->info('');
        $this->command->info(' Tenant created: ' . $tenant->name);
        $this->command->info('');

        $this->command->table(
            ['User', 'Email', 'Password', 'Role'],
            [
                ['Admin Liga',         'admin@demo.com',    'password', 'admin'],
                ['Carlos Coordinador', 'manager@demo.com',  'password', 'manager'],
                ['Roberto Árbitro',    'referee@demo.com',  'password', 'referee'],
                ['Miguel Delegado',    'delegate@demo.com', 'password', 'delegate'],
            ]
        );
    }
}
