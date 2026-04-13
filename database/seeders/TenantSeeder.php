<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\Tenant;
use App\Models\User;
use App\Services\RolePermissionService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        $roleService = app(RolePermissionService::class);

        // ── 1. Demo Tenant ──────────────────────────
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

        // Setup roles for this tenant
        $roleService->setupTenantRoles($tenant->id);
        setPermissionsTeamId($tenant->id);

        // ── 2. Admin user ───────────────────────────
        $admin = User::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name'      => 'Admin Liga',
            'email'     => 'admin@demo.com',
            'password'  => Hash::make('password'),
            'phone'     => '9931000001',
            'status'    => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole(RoleEnum::ADMIN->value);

        // ── 3. Manager user ─────────────────────────
        $manager = User::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name'      => 'Carlos Coordinador',
            'email'     => 'manager@demo.com',
            'password'  => Hash::make('password'),
            'status'    => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
        $manager->assignRole(RoleEnum::MANAGER->value);

        // ── 4. Referee user ─────────────────────────
        $referee = User::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name'      => 'Roberto Árbitro',
            'email'     => 'referee@demo.com',
            'password'  => Hash::make('password'),
            'status'    => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
        $referee->assignRole(RoleEnum::REFEREE->value);

        // ── 5. Delegate user ────────────────────────
        $delegate = User::withoutGlobalScopes()->create([
            'tenant_id' => $tenant->id,
            'name'      => 'Miguel Delegado',
            'email'     => 'delegate@demo.com',
            'password'  => Hash::make('password'),
            'status'    => User::STATUS_ACTIVE,
            'email_verified_at' => now(),
        ]);
        $delegate->assignRole(RoleEnum::DELEGATE->value);

        // ── Summary ─────────────────────────────────
        $this->command->info('');
        $this->command->info('✅ Tenant created: ' . $tenant->name);
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
