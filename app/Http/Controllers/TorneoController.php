<?php

namespace App\Http\Controllers;

use App\Models\Torneo;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;

class TorneoController extends Controller
{
    public function index(Request $request)
    {
        $constants = config('constants');

        return Inertia::render('Torneos/Index', [
            'torneos' => Torneo::query()
                ->when($request->search, function ($query) use ($request) {
                    $query->where('nombre', 'like', "%{$request->search}%");
                })
                ->when($request->tipo && $request->tipo !== 'todos', function ($query) use ($request) {
                    $query->where('tipo', $request->tipo);
                })
                ->latest()
                ->paginate(4)
                ->withQueryString(),

            'filters' => $request->only(['search', 'tipo']),
            'constantes' => $constants ?? [],
        ]);
    }

    public function create()
    {
        return Inertia::render('Torneos/Create', [
            'constantes' => config('constants') ?? [],
            'isEditing' => false
        ]);
    }

    public function edit(Torneo $torneo)
    {
        return Inertia::render('Torneos/Edit', [
            'torneo' => $torneo,
            'constantes' => config('constants') ?? [],
            'isEditing' => true
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $constants = config('constants');

        $validated = $request->validate([
            'nombre'      => 'required|string|max:255',
            'tipo'        => 'required|in:' . implode(',', array_keys($constants['tipos_torneo'] ?? [])),
            'categoria'   => 'required|in:' . implode(',', array_keys($constants['categorias'] ?? [])),
            'rama'        => 'required|in:' . implode(',', array_keys($constants['ramas'] ?? [])),
            'fecha_inicio' => 'required|date',
            'fecha_fin'   => 'nullable|date',
            'estado'      => 'required|in:activo,finalizado,cancelado',
            'reglas'      => 'nullable|string'
        ]);

        Torneo::create([
            ...$validated,
            'created_by' => auth()->id(),

        ]);

        return redirect()->route('torneos.index')
            ->with('success', 'Torneo creado correctamente');
    }

    public function update(Request $request, Torneo $torneo): RedirectResponse
    {
        $validated = $request->validate([
            'nombre'      => 'required|string|max:255',
            'tipo'        => 'required',
            'categoria'   => 'required',
            'rama'        => 'required',
            'fecha_inicio' => 'required|date',
            'fecha_fin'   => 'nullable|date',
            'estado'      => 'required',
            'reglas'      => 'nullable|string'
        ]);

        $torneo->update($validated);

        return redirect()->route('torneos.index')
            ->with('success', 'Torneo actualizado correctamente');
    }

    public function destroy(Torneo $torneo): RedirectResponse
    {
        $torneo->delete();

        return redirect()->route('torneos.index')
            ->with('success', 'Torneo eliminado correctamente');
    }
}
