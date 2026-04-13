<?php

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    /**
     * List users for the current tenant.
     */
    public function index(Request $request): Response
    {
        $this->authorize('users.view');

        $users = User::query()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($request->role && $request->role !== 'all', function ($query) use ($request) {
                $query->role($request->role); // Spatie scope
            })
            ->when($request->status && $request->status !== 'all', function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->latest()
            ->paginate(5)
            ->withQueryString()
            ->through(function ($user) {
                return [
                    'id'     => $user->id,
                    'name'   => $user->name,
                    'email'  => $user->email,
                    'phone'  => $user->phone,
                    'avatar' => $user->avatar,
                    'status' => $user->status,
                    'roles'  => $user->getRoleNames(),
                    'created_at' => $user->created_at->format('d/m/Y'),
                ];
            });

        return Inertia::render('Users/Index', [
            'users'   => $users,
            'roles'   => collect(RoleEnum::assignable())->map(fn ($r) => [
                'value' => $r->value,
                'label' => $r->label(),
            ]),
            'filters' => $request->only(['search', 'role', 'status']),
        ]);
    }

    /**
     * Show create form.
     */
    public function create(): Response
    {
        $this->authorize('users.create');

        return Inertia::render('Users/Create', [
            'roles' => collect(RoleEnum::assignable())->map(fn ($r) => [
                'value' => $r->value,
                'label' => $r->label(),
            ]),
        ]);
    }

    /**
     * Store a new user in the current tenant.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorize('users.create');

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'role'     => ['required', 'string', Rule::in(array_map(fn ($r) => $r->value, RoleEnum::assignable()))],
            'password' => ['required', Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'status'   => User::STATUS_ACTIVE,
            // tenant_id is auto-filled by BelongsToTenant trait
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    /**
     * Show edit form.
     */
    public function edit(User $user): Response
    {
        $this->authorize('users.update');

        return Inertia::render('Users/Edit', [
            'user' => [
                'id'     => $user->id,
                'name'   => $user->name,
                'email'  => $user->email,
                'phone'  => $user->phone,
                'status' => $user->status,
                'role'   => $user->getRoleNames()->first() ?? '',
            ],
            'roles' => collect(RoleEnum::assignable())->map(fn ($r) => [
                'value' => $r->value,
                'label' => $r->label(),
            ]),
        ]);
    }

    /**
     * Update user.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $this->authorize('users.update');

        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:150'],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone'    => ['nullable', 'string', 'max:20'],
            'role'     => ['required', 'string', Rule::in(array_map(fn ($r) => $r->value, RoleEnum::assignable()))],
            'status'   => ['required', 'string', Rule::in(['active', 'inactive', 'suspended'])],
            'password' => ['nullable', Password::defaults()],
        ]);

        $user->update([
            'name'   => $validated['name'],
            'email'  => $validated['email'],
            'phone'  => $validated['phone'] ?? null,
            'status' => $validated['status'],
        ]);

        if (!empty($validated['password'])) {
            $user->update(['password' => Hash::make($validated['password'])]);
        }

        // Sync role (remove old, assign new)
        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    /**
     * Soft delete user.
     */
    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('users.delete');

        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return back()->with('error', 'No puedes eliminarte a ti mismo.');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }
}
