<?php

namespace App\Http\Controllers;

use App\Enums\PermissionEnum;
use App\Enums\RoleEnum;
use App\Models\User;
use App\Services\RolePermissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserRoleController extends Controller
{
    public function __construct(
        private readonly RolePermissionService $service
    ) {}

    /**
     * Show user's permissions breakdown.
     */
    public function show(User $user): Response
    {
        $this->authorize('users.view');

        return Inertia::render('Roles/UserPermissions', [
            'targetUser'  => $user->only('id', 'name', 'email', 'avatar', 'status'),
            'permissions' => $this->service->getUserPermissions($user),
            'allRoles'    => $this->service->getTenantRoles(),
            'allPermissions' => $this->service->getPermissionsGrouped(),
        ]);
    }

    /**
     * Assign a role to a user.
     */
    public function assign(Request $request, User $user): RedirectResponse
    {
        $this->authorize('roles.update');

        $validated = $request->validate([
            'role' => [
                'required',
                'string',
                Rule::in(array_map(fn ($r) => $r->value, RoleEnum::assignable())),
            ],
        ]);

        $this->service->assignRoleToUser($user, $validated['role']);

        return back()->with('success', "Role '{$validated['role']}' assigned to {$user->name}.");
    }

    /**
     * Remove a role from a user.
     */
    public function revoke(Request $request, User $user): RedirectResponse
    {
        $this->authorize('roles.update');

        $validated = $request->validate([
            'role' => ['required', 'string'],
        ]);

        $this->service->removeRoleFromUser($user, $validated['role']);

        return back()->with('success', "Role '{$validated['role']}' removed from {$user->name}.");
    }

    /**
     * Give direct permission to user.
     */
    public function givePermission(Request $request, User $user): RedirectResponse
    {
        $this->authorize('roles.update');

        $validated = $request->validate([
            'permission' => ['required', 'string', Rule::in(PermissionEnum::all())],
        ]);

        $this->service->giveDirectPermission($user, $validated['permission']);

        return back()->with('success', 'Permission granted.');
    }

    /**
     * Revoke direct permission from user.
     */
    public function revokePermission(Request $request, User $user): RedirectResponse
    {
        $this->authorize('roles.update');

        $validated = $request->validate([
            'permission' => ['required', 'string'],
        ]);

        $this->service->revokeDirectPermission($user, $validated['permission']);

        return back()->with('success', 'Permission revoked.');
    }
}
