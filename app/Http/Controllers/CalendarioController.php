<?php

namespace App\Http\Controllers;

use App\Enums\PermissionEnum;
use App\Http\Requests\StoreSustitucionRequest;
use App\Models\Arbitro;
use App\Models\Cancha;
use App\Models\Partido;
use App\Models\PartidoSustitucion;
use App\Models\Scopes\TenantScope;
use App\Models\Torneo;
use App\Services\CalendarioService;
use App\Services\MatchSchedulingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class CalendarioController extends Controller
{
    public function __construct(
        protected CalendarioService $calendarioService,
        protected MatchSchedulingService $schedulingService
    ) {}

    public function show(Torneo $torneo)
    {
        $this->authorize(PermissionEnum::CALENDAR_MANAGE);

        $jornadas = $torneo->jornadas()
            ->with(['partidos' => function ($q) {
                $q->with([
                    'equipoLocal' => fn ($query) => $query->withoutGlobalScope(TenantScope::class),
                    'equipoLocal.equipo' => fn ($query) => $query->withoutGlobalScope(TenantScope::class),
                    'equipoVisitante' => fn ($query) => $query->withoutGlobalScope(TenantScope::class),
                    'equipoVisitante.equipo' => fn ($query) => $query->withoutGlobalScope(TenantScope::class),
                    'cancha',
                    'arbitro',
                ])->orderBy('hora');
            }])
            ->orderBy('numero')
            ->get();

        $partidosPlayoff = $torneo->partidos()
            ->where('fase', '!=', 'regular')
            ->with([
                'equipoLocal' => fn ($query) => $query->withoutGlobalScope(TenantScope::class),
                'equipoLocal.equipo' => fn ($query) => $query->withoutGlobalScope(TenantScope::class),
                'equipoVisitante' => fn ($query) => $query->withoutGlobalScope(TenantScope::class),
                'equipoVisitante.equipo' => fn ($query) => $query->withoutGlobalScope(TenantScope::class),
                'cancha',
                'arbitro',
            ])
            ->orderBy('fase')
            ->orderBy('llave_bracket')
            ->orderBy('orden_bracket')
            ->get()
            ->groupBy('fase');

        $canchas = Cancha::where('tenant_id', $torneo->tenant_id)
            ->where('estado', 'activo')
            ->get(['id', 'nombre', 'tipo']);

        $arbitros = Arbitro::where('tenant_id', $torneo->tenant_id)
            ->where('disponible', true)
            ->get(['id', 'nombre']);

        $equiposDisponibles = $torneo->inscripciones()
            ->aprobados()
            ->with('equipo')
            ->get();

        return Inertia::render('Calendario/Show', [
            'torneo' => $torneo->only([
                'id', 'nombre', 'tipo', 'estado', 'fecha_inicio', 'fecha_fin',
                'ida_y_vuelta', 'formato_relampago', 'tiene_playoff',
                'playoff_equipos', 'playoff_ida_vuelta',
                'hora_inicio', 'duracion_minutos',
            ]),
            'jornadas' => $jornadas->map(fn ($j) => [
                'id' => $j->id,
                'nombre' => $j->nombre,
                'numero' => $j->numero,
                'fecha_inicio' => $j->fecha_inicio?->format('Y-m-d'),
                'estado' => $j->estado,
                'partidos' => $j->partidos->map(fn ($p) => $this->mapPartido($p)),
            ]),
            'bracket' => $partidosPlayoff->map(fn ($partidos, $fase) => [
                'fase' => $fase,
                'label' => config("constants.fases_partido.{$fase}", ucfirst($fase)),
                'partidos' => $partidos->map(fn ($p) => $this->mapPartido($p))->values(),
            ])->values(),
            'canchas' => $canchas,
            'arbitros' => $arbitros,
            'equipos_disponibles' => $equiposDisponibles->map(fn ($te) => [
                'id' => $te->id,
                'team_id' => $te->team_id,
                'nombre' => $te->equipo->name ?? 'Equipo #'.$te->team_id,
            ]),
            'constantes' => [
                'fases_partido' => config('constants.fases_partido'),
                'motivos_sustitucion' => config('constants.motivos_sustitucion'),
                'tipos_resolucion' => config('constants.tipos_resolucion_sustitucion'),
            ],
        ]);
    }

    public function preview(Request $request, Torneo $torneo)
    {
        $this->authorize(PermissionEnum::CALENDAR_MANAGE);

        try {
            $preview = $this->calendarioService->preview($torneo);
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first();

            return redirect()->route('calendario.show', $torneo)
                ->with('error', $message ?? 'No se pudo generar el calendario.');
        }

        return Inertia::render('Calendario/Preview', [
            'torneo' => $torneo->only([
                'id', 'nombre', 'tipo', 'estado', 'fecha_inicio', 'fecha_fin',
                'ida_y_vuelta', 'formato_relampago', 'tiene_playoff',
                'playoff_equipos', 'playoff_ida_vuelta',
                'hora_inicio', 'duracion_minutos',
            ]),
            'preview' => $preview,
            'constantes' => [
                'fases_partido' => config('constants.fases_partido'),
            ],
        ]);
    }

    public function store(Request $request, Torneo $torneo)
    {
        $this->authorize(PermissionEnum::CALENDAR_MANAGE);

        $validated = $request->validate([
            'ajustes' => 'required|array',
            'ajustes.jornadas' => 'array',
            'ajustes.partidos' => 'array',
            'ajustes.bracket' => 'array',
        ]);

        $this->calendarioService->confirmar($torneo, $validated['ajustes']);

        return redirect()->route('calendario.show', $torneo)
            ->with('success', 'Calendario generado correctamente.');
    }

    public function update(Request $request, Torneo $torneo)
    {
        $this->authorize(PermissionEnum::CALENDAR_MANAGE);

        $validated = $request->validate([
            'partidos' => 'required|array',
            'partidos.*.id' => 'required|integer|exists:partidos,id',
            'partidos.*.cancha_id' => 'nullable|integer|exists:canchas,id',
            'partidos.*.fecha' => 'nullable|date',
            'partidos.*.hora' => 'nullable|date_format:H:i',
        ]);

        foreach ($validated['partidos'] as $partidoData) {
            $partido = Partido::findOrFail($partidoData['id']);

            if ($partido->torneo_id !== $torneo->id) {
                continue;
            }

            if ($partido->estado !== 'programado') {
                throw ValidationException::withMessages([
                    'partidos' => 'Solo se pueden editar partidos programados.',
                ]);
            }

            if (! empty($partidoData['cancha_id']) && (empty($partidoData['fecha']) || empty($partidoData['hora']))) {
                throw ValidationException::withMessages([
                    'partidos' => 'La fecha y hora son obligatorias cuando se asigna una cancha.',
                ]);
            }

            $updateData = [
                'torneo_id' => $partido->torneo_id,
                'jornada_id' => $partido->jornada_id,
                'equipo_local_id' => $partido->equipo_local_id,
                'equipo_visitante_id' => $partido->equipo_visitante_id,
                'fase' => $partido->fase,
                'es_vuelta' => $partido->es_vuelta,
                'llave_bracket' => $partido->llave_bracket,
                'orden_bracket' => $partido->orden_bracket,
                'cancha_id' => array_key_exists('cancha_id', $partidoData)
                    ? $partidoData['cancha_id']
                    : $partido->cancha_id,
                'arbitro_id' => $partido->arbitro_id,
                'fecha' => array_key_exists('fecha', $partidoData)
                    ? $partidoData['fecha']
                    : $partido->fecha?->format('Y-m-d'),
                'hora' => array_key_exists('hora', $partidoData)
                    ? $partidoData['hora']
                    : $partido->hora?->format('H:i'),
                'duracion_minutos' => $partido->duracion_minutos ?? 90,
                'estado' => $partido->estado,
            ];

            $this->schedulingService->actualizar($partido, $updateData);
        }

        return redirect()->route('calendario.show', $torneo)
            ->with('success', 'Calendario actualizado correctamente.');
    }

    public function destroy(Torneo $torneo)
    {
        $this->authorize(PermissionEnum::CALENDAR_MANAGE);

        $this->calendarioService->eliminar($torneo);

        return redirect()->route('calendario.show', $torneo)
            ->with('success', 'Calendario eliminado correctamente.');
    }

    public function sustituir(StoreSustitucionRequest $request, Partido $partido)
    {
        $this->authorize(PermissionEnum::CALENDAR_MANAGE);

        if ($partido->estado !== 'programado') {
            throw ValidationException::withMessages([
                'partido' => 'Solo se pueden sustituir equipos en partidos programados.',
            ]);
        }

        $validated = $request->validated();

        $esLocal = $validated['equipo_original_id'] === $partido->equipo_local_id;
        $campoEquipo = $esLocal ? 'equipo_local_id' : 'equipo_visitante_id';

        $nuevoLocalId = $esLocal
            ? $validated['equipo_sustituto_id']
            : $partido->equipo_local_id;
        $nuevoVisitanteId = $esLocal
            ? $partido->equipo_visitante_id
            : $validated['equipo_sustituto_id'];

        $duplicado = null;
        if ($partido->fase === 'regular') {
            $duplicado = Partido::query()
                ->where('torneo_id', $partido->torneo_id)
                ->where('fase', 'regular')
                ->where('estado', 'programado')
                ->where('id', '!=', $partido->id)
                ->where('equipo_local_id', $nuevoLocalId)
                ->where('equipo_visitante_id', $nuevoVisitanteId)
                ->first();
        }

        $intercambioRealizado = false;

        DB::transaction(function () use ($partido, $validated, $duplicado, $campoEquipo, $nuevoLocalId, $nuevoVisitanteId, &$intercambioRealizado) {
            $partidoReprogramado = null;

            if ($validated['tipo_resolucion'] === 'reprogramado') {
                $partidoReprogramado = Partido::create([
                    'torneo_id' => $partido->torneo_id,
                    'jornada_id' => $partido->jornada_id,
                    'equipo_local_id' => $nuevoLocalId,
                    'equipo_visitante_id' => $nuevoVisitanteId,
                    'cancha_id' => $validated['cancha_id'] ?? $partido->cancha_id,
                    'arbitro_id' => $validated['arbitro_id'] ?? $partido->arbitro_id,
                    'fecha' => $validated['nueva_fecha'],
                    'hora' => $validated['nueva_hora'],
                    'duracion_minutos' => $partido->duracion_minutos,
                    'estado' => 'programado',
                    'fase' => $partido->fase,
                    'es_vuelta' => $partido->es_vuelta,
                    'llave_bracket' => $partido->llave_bracket,
                    'orden_bracket' => $partido->orden_bracket,
                ]);

                $partido->update(['estado' => 'cancelado']);
            } else {
                $partido->update([
                    'equipo_local_id' => $nuevoLocalId,
                    'equipo_visitante_id' => $nuevoVisitanteId,
                ]);
            }

            PartidoSustitucion::create([
                'partido_id' => $partido->id,
                'equipo_original_id' => $validated['equipo_original_id'],
                'equipo_sustituto_id' => $validated['equipo_sustituto_id'],
                'motivo' => $validated['motivo'],
                'tipo_resolucion' => $validated['tipo_resolucion'],
                'partido_reprogramado_id' => $partidoReprogramado?->id,
                'notas' => $validated['notas'] ?? null,
                'created_by' => auth()->id(),
            ]);

            if ($duplicado) {
                $duplicado->update([
                    $campoEquipo => $validated['equipo_original_id'],
                ]);

                PartidoSustitucion::create([
                    'partido_id' => $duplicado->id,
                    'equipo_original_id' => $validated['equipo_sustituto_id'],
                    'equipo_sustituto_id' => $validated['equipo_original_id'],
                    'motivo' => $validated['motivo'],
                    'tipo_resolucion' => $validated['tipo_resolucion'],
                    'partido_reprogramado_id' => null,
                    'notas' => 'Intercambio automático generado por sustitución en partido #'.$partido->id,
                    'created_by' => auth()->id(),
                ]);

                $intercambioRealizado = true;
            }
        });

        $mensaje = 'Sustitución registrada correctamente.';
        if ($intercambioRealizado) {
            $mensaje .= ' Se intercambiaron equipos automáticamente en otra jornada para evitar enfrentamientos repetidos.';
        }

        return redirect()->back()->with('success', $mensaje);
    }

    protected function mapPartido(Partido $p): array
    {
        return [
            'id' => $p->id,
            'equipo_local' => $p->equipoLocal ? [
                'id' => $p->equipoLocal->id,
                'nombre' => $p->equipoLocal->equipo?->name ?? 'TBD',
                'escudo' => $p->equipoLocal->equipo?->shield ?? null,
            ] : null,
            'equipo_visitante' => $p->equipoVisitante ? [
                'id' => $p->equipoVisitante->id,
                'nombre' => $p->equipoVisitante->equipo?->name ?? 'TBD',
                'escudo' => $p->equipoVisitante->equipo?->shield ?? null,
            ] : null,
            'cancha' => $p->cancha?->only(['id', 'nombre']),
            'arbitro' => $p->arbitro ? [
                'id' => $p->arbitro->id,
                'nombre' => $p->arbitro->nombre,
            ] : null,
            'fecha' => $p->fecha?->format('Y-m-d'),
            'hora' => $p->hora?->format('H:i'),
            'estado' => $p->estado,
            'fase' => $p->fase,
            'es_vuelta' => $p->es_vuelta,
            'llave_bracket' => $p->llave_bracket,
            'orden_bracket' => $p->orden_bracket,
            'goles_local' => $p->goles_local,
            'goles_visitante' => $p->goles_visitante,
            'duracion_minutos' => $p->duracion_minutos,
        ];
    }
}
