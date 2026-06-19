<?php

namespace App\Http\Controllers;

use App\Enums\PermissionEnum;
use App\Models\Torneo;
use App\Services\TorneoInscripcionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'isEditing' => false,
        ]);
    }

    public function edit(Torneo $torneo)
    {
        $this->authorize(PermissionEnum::TOURNAMENTS_UPDATE);

        return Inertia::render('Torneos/Edit', [
            'torneo' => $torneo,
            'constantes' => config('constants') ?? [],
            'isEditing' => true,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize(PermissionEnum::TOURNAMENTS_CREATE);

        $constants = config('constants');

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required|in:'.implode(',', array_keys($constants['tipos_torneo'] ?? [])),
            'categoria' => 'required|in:'.implode(',', array_keys($constants['categorias'] ?? [])),
            'rama' => 'required|in:'.implode(',', array_keys($constants['ramas'] ?? [])),
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date|after_or_equal:fecha_inicio',
            'estado' => 'required|in:activo,finalizado,cancelado',
            'reglas' => 'nullable|string',
            'max_equipos' => 'nullable|integer|min:2|max:999',
            'inscripcion_abierta' => 'boolean',
            'fair_play_automatico' => 'boolean',
            'ida_y_vuelta' => 'boolean',
            'formato_relampago' => 'nullable|in:grupos,eliminacion_directa',
            'tiene_playoff' => 'boolean',
            'playoff_equipos' => 'nullable|integer|in:2,4,8,16',
            'playoff_ida_vuelta' => 'boolean',
            'hora_inicio' => 'nullable|date_format:H:i',
            'duracion_minutos' => 'nullable|integer|min:15|max:300',
        ]);

        $torneo = Torneo::create([
            ...$validated,
            'created_by' => auth()->id(),
            'inscripcion_abierta' => $request->boolean('inscripcion_abierta', true),
            'fair_play_automatico' => $request->boolean('fair_play_automatico', false),
            'ida_y_vuelta' => $request->boolean('ida_y_vuelta', false),
            'tiene_playoff' => $request->boolean('tiene_playoff', false),
            'playoff_ida_vuelta' => $request->boolean('playoff_ida_vuelta', false),
            'hora_inicio' => $request->input('hora_inicio') ?? '12:00',
            'duracion_minutos' => $request->input('duracion_minutos') ?? 90,
        ]);

        $this->inscripcionService->ensureGrupoGeneral($torneo);

        return redirect()->route('torneos.index')
            ->with('success', 'Torneo creado correctamente');
    }

    public function update(Request $request, Torneo $torneo): RedirectResponse
    {
        $this->authorize(PermissionEnum::TOURNAMENTS_UPDATE);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'tipo' => 'required',
            'categoria' => 'required',
            'rama' => 'required',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'nullable|date',
            'estado' => 'required|in:activo,finalizado,cancelado',
            'reglas' => 'nullable|string',
            'max_equipos' => 'nullable|integer|min:2|max:999',
            'inscripcion_abierta' => 'boolean',
            'fair_play_automatico' => 'boolean',
            'ida_y_vuelta' => 'boolean',
            'formato_relampago' => 'nullable|in:grupos,eliminacion_directa',
            'tiene_playoff' => 'boolean',
            'playoff_equipos' => 'nullable|integer|in:2,4,8,16',
            'playoff_ida_vuelta' => 'boolean',
            'hora_inicio' => 'nullable|date_format:H:i',
            'duracion_minutos' => 'nullable|integer|min:15|max:300',
        ]);

        $torneo->update([
            ...$validated,
            'inscripcion_abierta' => $request->boolean('inscripcion_abierta', $torneo->inscripcion_abierta),
            'fair_play_automatico' => $request->boolean('fair_play_automatico', $torneo->fair_play_automatico),
            'ida_y_vuelta' => $request->boolean('ida_y_vuelta', $torneo->ida_y_vuelta),
            'tiene_playoff' => $request->boolean('tiene_playoff', $torneo->tiene_playoff),
            'playoff_ida_vuelta' => $request->boolean('playoff_ida_vuelta', $torneo->playoff_ida_vuelta),
            'hora_inicio' => $request->input('hora_inicio') ?? $torneo->hora_inicio,
            'duracion_minutos' => $request->input('duracion_minutos') ?? $torneo->duracion_minutos,
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
