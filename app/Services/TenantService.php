<?php

use App\DTOs\TenantDTO;
use App\Models\Tenant;
use App\Services\RolePermissionService;

class TenantService
{
    public function __construct(
        private readonly RolePermissionService $roleService
    ) {}

    public function create(TenantDTO $dto): Tenant
    {
        $tenant = Tenant::create([
            'name' => $dto->name,
            'slug' => $dto->slug,
            'plan' => $dto->plan,
            'status' => 'trial',
        ]);

        // Bootstrap default roles & permissions for this tenant
        $this->roleService->setupTenantRoles($tenant->id);

        return $tenant;
    }
}
