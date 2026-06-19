<?php

namespace App\Http\Controllers;

use App\Enums\PermissionEnum;
use App\Http\Requests\StoreTorneoEquipoRequest;
use App\Http\Requests\UpdateTorneoEquipoRequest;
use App\Models\Team;
use App\Models\Torneo;
use App\Models\TorneoEquipo;
use App\Services\TorneoInscripcionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TorneoEquipoController extends Controller
{
    public function __construct(
        private readonly TorneoInscripcionService $inscripcionService
    ) {}

    public function index(Request $request, Torneo $torneo): Response
    {
        $this->authorize('viewAny', TorneoEquipo::class);

        $this->inscripcionService->ensureGrupoGeneral($torneo);

        $inscripciones = $torneo->inscripciones()
            ->with(['equipo:id,name,shield,colors,delegado_id', 'grupo:id,nombre', 'aprobadoPor:id,name'])
            ->when($request->estado && $request->estado !== 'todos', fn ($q) => $q->where('estado', $request->estado))
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('equipo', fn ($eq) => $eq->where('name', 'like', '%'.$request->search.'%'));
            })
            ->orderByRaw("FIELD(estado, 'pendiente', 'aprobado', 'rechazado', 'retirado', 'descalificado')")
            ->ordenadosPorSeed()
            ->paginate(15)
            ->withQueryString();

        $equiposInscritosIds = $torneo->inscripciones()
            ->whereNotIn('estado', ['rechazado', 'retirado'])
            ->pluck('team_id');

        $equiposDisponibles = Team::query()
            ->active()
            ->whereNotIn('id', $equiposInscritosIds)
            ->orderBy('name')
            ->get(['id', 'name']);

        $grupos = $torneo->grupos()->orderBy('orden')->orderBy('nombre')->get(['id', 'nombre', 'orden']);
        $grupoDefaultId = $grupos->first()?->id;

        return Inertia::render('Torneos/Inscripciones', [
            'torneo' => $torneo->only([
                'id',
                'nombre',
                'tipo',
                'categoria',
                'rama',
                'estado',
                'max_equipos',
                'inscripcion_abierta',
            ]),
            'inscripciones' => $inscripciones,
            'grupos' => $grupos,
            'grupoDefaultId' => $grupoDefaultId,
            'equiposDisponibles' => $equiposDisponibles,
            'cupo' => [
                'ocupados' => $this->inscripcionService->contarCupoOcupado($torneo),
                'max' => $torneo->max_equipos,
            ],
            'filters' => $request->only(['search', 'estado']),
            'constantes' => [
                'estados_inscripcion' => config('constants.estados_inscripcion', []),
            ],
            'can' => [
                'create' => auth()->user()->can('create', [TorneoEquipo::class, $torneo]),
                'update' => auth()->user()->can(PermissionEnum::TOURNAMENTS_UPDATE),
            ],
        ]);
    }

    public function store(StoreTorneoEquipoRequest $request, Torneo $torneo): RedirectResponse
    {
        $this->authorize('create', [TorneoEquipo::class, $torneo]);

        $this->inscripcionService->inscribir(
            $torneo,
            $request->validated(),
            $request->user()
        );

        return redirect()
            ->route('torneos.equipos.index', $torneo)
            ->with('success', 'Inscripción registrada correctamente.');
    }

    public function update(
        UpdateTorneoEquipoRequest $request,
        Torneo $torneo,
        TorneoEquipo $torneoEquipo
    ): RedirectResponse {
        $inscripcion = $this->resolveInscripcion($torneo, $torneoEquipo);
        $this->authorize('update', $inscripcion);

        $data = $request->validated();

        if (array_key_exists('torneo_grupo_id', $data) && $data['torneo_grupo_id'] !== null) {
            $this->inscripcionService->asignarGrupo($inscripcion, (int) $data['torneo_grupo_id']);
        }

        if (array_key_exists('seed', $data)) {
            $this->inscripcionService->asignarSeed($inscripcion, $data['seed']);
        }

        if (array_key_exists('notas', $data)) {
            $inscripcion->update(['notas' => $data['notas']]);
        }

        return redirect()
            ->route('torneos.equipos.index', $torneo)
            ->with('success', 'Inscripción actualizada correctamente.');
    }

    public function aprobar(Torneo $torneo, TorneoEquipo $torneoEquipo): RedirectResponse
    {
        $inscripcion = $this->resolveInscripcion($torneo, $torneoEquipo);
        $this->authorize('approve', $inscripcion);

        $this->inscripcionService->aprobar($inscripcion, auth()->user());

        return redirect()
            ->route('torneos.equipos.index', $torneo)
            ->with('success', 'Inscripción aprobada.');
    }

    public function rechazar(Request $request, Torneo $torneo, TorneoEquipo $torneoEquipo): RedirectResponse
    {
        $inscripcion = $this->resolveInscripcion($torneo, $torneoEquipo);
        $this->authorize('reject', $inscripcion);

        $request->validate([
            'motivo_rechazo' => ['nullable', 'string', 'max:255'],
        ]);

        $this->inscripcionService->rechazar($inscripcion, $request->input('motivo_rechazo'));

        return redirect()
            ->route('torneos.equipos.index', $torneo)
            ->with('success', 'Inscripción rechazada.');
    }

    public function asignarGrupo(Request $request, Torneo $torneo, TorneoEquipo $torneoEquipo): RedirectResponse
    {
        $inscripcion = $this->resolveInscripcion($torneo, $torneoEquipo);
        $this->authorize('assignGrupo', $inscripcion);

        $request->validate([
            'torneo_grupo_id' => ['required', 'integer', 'exists:torneo_grupos,id'],
        ]);

        $this->inscripcionService->asignarGrupo($inscripcion, (int) $request->input('torneo_grupo_id'));

        return redirect()
            ->route('torneos.equipos.index', $torneo)
            ->with('success', 'Grupo asignado correctamente.');
    }

    public function asignarSeed(Request $request, Torneo $torneo, TorneoEquipo $torneoEquipo): RedirectResponse
    {
        $inscripcion = $this->resolveInscripcion($torneo, $torneoEquipo);
        $this->authorize('assignSeed', $inscripcion);

        $request->validate([
            'seed' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);

        $this->inscripcionService->asignarSeed(
            $inscripcion,
            $request->filled('seed') ? (int) $request->input('seed') : null
        );

        return redirect()
            ->route('torneos.equipos.index', $torneo)
            ->with('success', 'Seed asignado correctamente.');
    }

    protected function resolveInscripcion(Torneo $torneo, TorneoEquipo $torneoEquipo): TorneoEquipo
    {
        abort_unless($torneoEquipo->torneo_id === $torneo->id, 404);

        return $torneoEquipo;
    }
}
