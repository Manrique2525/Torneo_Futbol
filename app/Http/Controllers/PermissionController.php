<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionController extends Controller
{
    public function index(Request $request): Response
    {
        $this->authorize('permissions.view');

        return Inertia::render('Permissions/Index', [
            // Pasamos los filtros actuales para que el componente Vue los use en los inputs
            'filters' => $request->only(['search']),

            'permissions' => Permission::query()
                ->when($request->input('search'), function ($query, $search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('guard_name', 'like', "%{$search}%");
                })
                ->orderBy('name')
                ->paginate(5) // 10 registros por página
                ->withQueryString() // Mantiene los filtros en los enlaces de paginación
                ->through(fn($p) => [
                    'id'         => $p->id,
                    'name'       => $p->name,
                    'guard_name' => $p->guard_name,
                    'created_at' => $p->created_at->format('d/m/Y'),
                ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('permissions.create');

        // Cambia la validación para que acepte 'name'
        $request->validate([
            'name' => 'required|string|unique:permissions,name',
        ]);

        Permission::firstOrCreate([
            'name' => $request->name,
            'guard_name' => 'web',
        ]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return back()->with('success', 'Permission created');
    }

    public function destroy(Permission $permission)
    {
        $this->authorize('permissions.delete');

        $permission->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return back()->with('success', 'Permission deleted');
    }
}
