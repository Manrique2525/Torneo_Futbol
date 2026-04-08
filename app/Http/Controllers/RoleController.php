<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRolePermissionsRequest;
use App\Services\RolePermissionService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function __construct(
        private readonly RolePermissionService $service
    ) {}

    /**
     * List all roles with their permissions.
     */
    public function index(): Response
    {
        $this->authorize('roles.view');

        return Inertia::render('Roles/Index', [
            'roles'       => $this->service->getTenantRoles(),
            'permissions' => $this->service->getPermissionsGrouped(),
        ]);
    }

    /**
     * Create a custom role.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $this->service->createRole(
            $request->validated('name'),
            $request->validated('permissions', []),
        );

        return back()->with('success', 'Role created successfully.');
    }

    /**
     * Update permissions on a role.
     */
    public function update(UpdateRolePermissionsRequest $request, Role $role): RedirectResponse
    {
        $this->service->updateRolePermissions(
            $role,
            $request->validated('permissions', []),
        );

        return back()->with('success', 'Permissions updated successfully.');
    }

    /**
     * Delete a custom role.
     */
    public function destroy(Role $role): RedirectResponse
    {
        $this->authorize('roles.delete');

        $this->service->deleteRole($role);

        return back()->with('success', 'Role deleted successfully.');
    }
}
