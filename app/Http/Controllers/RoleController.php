<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRolePermissionsRequest;
use App\Services\RolePermissionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleController extends Controller
{
    public function __construct(
        private readonly RolePermissionService $service
    ) {}

    public function index(): Response
    {
        $this->authorize('roles.view');

        return Inertia::render('Roles/Index', [
            'roles' => $this->service->getRoles(),

            // 🔥 ahora viene directo de BD (NO ENUM)
            'permissions' => $this->service->getPermissionsGrouped(),
        ]);
    }

    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->authorize('roles.create');

        $permissions = $this->sanitizePermissions(
            $request->validated('permissions', [])
        );

        $this->service->createRole(
            $request->validated('name'),
            $permissions,
        );

        return back()->with('success', 'Role created successfully.');
    }

    public function update(UpdateRolePermissionsRequest $request, Role $role): RedirectResponse
    {
        $this->authorize('roles.update');

        $permissions = $this->sanitizePermissions(
            $request->validated('permissions', [])
        );

        $this->service->updateRolePermissions(
            $role,
            $permissions,
        );

        return back()->with('success', 'Permissions updated successfully.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('roles.delete');

        $this->service->deleteRole($role);

        return back()->with('success', 'Role deleted successfully.');
    }

    /**
     * 🔥 CLAVE: evita el error PermissionDoesNotExist
     */
    private function sanitizePermissions(array $permissions): array
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return Permission::whereIn('name', $permissions)
            ->pluck('name')
            ->toArray();
    }
}
