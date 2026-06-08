<?php

namespace App\Http\Controllers;

use App\Enums\PermissionEnum;
use App\Models\Torneo;
use App\Services\TorneoInscripcionService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class TorneoController extends Controller
{
    public function __construct(
        private readonly TorneoInscripcionService $inscripcionService
    ) {}

    public function index(Request $request)
    {
        $this->authorize(PermissionEnum::TOURNAMENTS_VIEW);

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
        $this->authorize(PermissionEnum::TOURNAMENTS_CREATE);

        return Inertia::render('Torneos/Create', [
            'constantes' => config('constants') ?? [],
            'isEditing' => false
        ]);
    }

    public function edit(Torneo $torneo)
    {
        $this->authorize(PermissionEnum::TOURNAMENTS_UPDATE);

        return Inertia::render('Torneos/Edit', [
            'torneo' => $torneo,
            'constantes' => config('constants') ?? [],
            'isEditing' => true
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize(PermissionEnum::TOURNAMENTS_CREATE);

        $constants = config('constants');

        $validated = $request->validate([
            'nombre'              => 'required|string|max:255',
            'tipo'                => 'required|in:'.implode(',', array_keys($constants['tipos_torneo'] ?? [])),
            'categoria'           => 'required|in:'.implode(',', array_keys($constants['categorias'] ?? [])),
            'rama'                => 'required|in:'.implode(',', array_keys($constants['ramas'] ?? [])),
            'fecha_inicio'        => 'required|date',
            'fecha_fin'           => 'nullable|date|after_or_equal:fecha_inicio',
            'estado'              => 'required|in:activo,finalizado,cancelado',
            'reglas'              => 'nullable|string',
            'max_equipos'         => 'nullable|integer|min:2|max:999',
            'inscripcion_abierta' => 'boolean',
            'fair_play_automatico' => 'boolean',
        ]);

        $torneo = Torneo::create([
            ...$validated,
            'created_by' => auth()->id(),
            'inscripcion_abierta' => $request->boolean('inscripcion_abierta', true),
            'fair_play_automatico' => $request->boolean('fair_play_automatico', false),
        ]);

        $this->inscripcionService->ensureGrupoGeneral($torneo);

        return redirect()->route('torneos.index')
            ->with('success', 'Torneo creado correctamente');
    }

    public function update(Request $request, Torneo $torneo): RedirectResponse
    {
        $this->authorize(PermissionEnum::TOURNAMENTS_UPDATE);

        $validated = $request->validate([
            'nombre'              => 'required|string|max:255',
            'tipo'                => 'required',
            'categoria'           => 'required',
            'rama'                => 'required',
            'fecha_inicio'        => 'required|date',
            'fecha_fin'           => 'nullable|date',
            'estado'              => 'required|in:activo,finalizado,cancelado',
            'reglas'              => 'nullable|string',
            'max_equipos'         => 'nullable|integer|min:2|max:999',
            'inscripcion_abierta' => 'boolean',
            'fair_play_automatico' => 'boolean',
        ]);

        $torneo->update([
            ...$validated,
            'inscripcion_abierta' => $request->boolean('inscripcion_abierta', $torneo->inscripcion_abierta),
            'fair_play_automatico' => $request->boolean('fair_play_automatico', $torneo->fair_play_automatico),
        ]);

        return redirect()->route('torneos.index')
            ->with('success', 'Torneo actualizado correctamente');
    }

    public function destroy(Torneo $torneo): RedirectResponse
    {
        $this->authorize(PermissionEnum::TOURNAMENTS_DELETE);

        $torneo->delete();

        return redirect()->route('torneos.index')
            ->with('success', 'Torneo eliminado correctamente');
    }
}
