<?php

namespace App\Http\Controllers;

use App\Enums\PermissionEnum;
use App\Http\Requests\StorePartidoRequest;
use App\Http\Requests\UpdatePartidoRequest;
use App\Models\Arbitro;
use App\Models\Cancha;
use App\Models\Jornada;
use App\Models\Partido;
use App\Models\Torneo;
use App\Models\TorneoEquipo;
use App\Services\MatchSchedulingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PartidoController extends Controller
{
    public function __construct(
        private readonly MatchSchedulingService $schedulingService
    ) {}

    public function index(Request $request)
    {
        $this->authorize(PermissionEnum::MATCHES_VIEW);

        $constants = config('constants');

        $torneos = Torneo::query()
            ->select('id', 'nombre', 'tipo_gestion')
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Partidos/Index', [
            'partidos' => Partido::query()
                ->with([
                    'torneo:id,nombre',
                    'jornada:id,nombre',
                    'equipoLocal.equipo:id,name,shield,delegado_id',
                    'equipoVisitante.equipo:id,name,shield,delegado_id',
                    'cancha:id,nombre',
                    'arbitro:id,nombre',
                ])
                ->when($request->search, function ($q) use ($request) {
                    $q->where(function ($sq) use ($request) {
                        $sq->whereHas('equipoLocal.equipo', fn ($eq) => $eq->where('name', 'like', "%{$request->search}%"))
                            ->orWhereHas('equipoVisitante.equipo', fn ($eq) => $eq->where('name', 'like', "%{$request->search}%"));
                    });
                })
                ->when($request->torneo_id && $request->torneo_id !== 'todos', function ($q) use ($request) {
                    $q->where('torneo_id', $request->torneo_id);
                })
                ->when($request->estado && $request->estado !== 'todos', function ($q) use ($request) {
                    $q->where('estado', $request->estado);
                })
                ->latest('fecha')
                ->paginate(10)
                ->withQueryString(),

            'filters' => $request->only(['search', 'torneo_id', 'estado']),
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
            'constantes' => $constants ?? [],
            'torneos' => $torneos,
        ]);
    }

    public function create()
    {
        $this->authorize(PermissionEnum::MATCHES_CREATE);

        $torneos = Torneo::query()
            ->select('id', 'nombre', 'tipo_gestion')
            ->orderBy('nombre')
            ->get()
            ->filter(fn ($t) => ! $t->esAuto())
            ->values();

        $canchas = Cancha::query()
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        $arbitros = Arbitro::where('tenant_id', auth()->user()->tenant_id)
            ->where('disponible', true)
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        $equipos = TorneoEquipo::query()
            ->with('equipo:id,name')
            ->with('torneo:id,nombre')
            ->where('estado', 'aprobado')
            ->get()
            ->map(fn ($te) => [
                'id' => $te->id,
                'nombre' => ($te->equipo?->name ?? 'Equipo #'.$te->id).' — '.($te->torneo?->nombre ?? 'Sin torneo'),
                'torneo_id' => $te->torneo_id,
            ]);

        return Inertia::render('Partidos/Create', [
            'torneos' => $torneos,
            'canchas' => $canchas,
            'arbitros' => $arbitros,
            'equipos' => $equipos,
            'constantes' => [
                'estados_partido' => config('constants.estados_partido', []),
            ],
        ]);
    }

    public function store(StorePartidoRequest $request): RedirectResponse
    {
        $torneo = Torneo::findOrFail($request->torneo_id);
        if ($torneo->esAuto()) {
            abort(403, 'No puedes crear partidos manuales en torneos con gestión automática de calendario.');
        }

        $this->schedulingService->programar($request->validated());

        return redirect()->route('partidos.index')
            ->with('success', 'Partido programado correctamente.');
    }

    public function edit(Partido $partido)
    {
        $this->authorize(PermissionEnum::MATCHES_UPDATE);

        $torneos = Torneo::query()
            ->select('id', 'nombre', 'tipo_gestion')
            ->orderBy('nombre')
            ->get()
            ->filter(fn ($t) => ! $t->esAuto() || $t->id === $partido->torneo_id)
            ->values();

        $canchas = Cancha::query()
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        $arbitros = Arbitro::where('tenant_id', auth()->user()->tenant_id)
            ->where('disponible', true)
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        $jornadas = Jornada::query()
            ->where('torneo_id', $partido->torneo_id)
            ->select('id', 'nombre')
            ->orderBy('numero')
            ->get();

        $equipos = TorneoEquipo::query()
            ->with('equipo:id,name')
            ->where('torneo_id', $partido->torneo_id)
            ->where('estado', 'aprobado')
            ->get()
            ->map(fn ($te) => [
                'id' => $te->id,
                'nombre' => $te->equipo?->name ?? 'Equipo #'.$te->id,
            ]);

        $partido->load(['equipoLocal.equipo:id,name', 'equipoVisitante.equipo:id,name']);

        return Inertia::render('Partidos/Edit', [
            'partido' => $partido,
            'torneos' => $torneos,
            'canchas' => $canchas,
            'arbitros' => $arbitros,
            'jornadas' => $jornadas,
            'equipos' => $equipos,
            'constantes' => [
                'estados_partido' => config('constants.estados_partido', []),
            ],
        ]);
    }

    public function update(UpdatePartidoRequest $request, Partido $partido): RedirectResponse
    {
        $this->schedulingService->actualizar($partido, $request->validated());

        return redirect()->route('partidos.index')
            ->with('success', 'Partido actualizado correctamente.');
    }

    public function destroy(Partido $partido): RedirectResponse
    {
        $this->authorize(PermissionEnum::MATCHES_DELETE);

        $partido->delete();

        return redirect()->route('partidos.index')
            ->with('success', 'Partido eliminado correctamente.');
    }
}
