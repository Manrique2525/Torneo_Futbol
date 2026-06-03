<?php

namespace App\Services;

use App\Enums\PartidoEventoTipoEnum;
use App\Models\Partido;
use App\Models\PartidoAsistencia;
use App\Models\PartidoEvento;
use App\Models\Player;
use Illuminate\Support\Facades\DB;

class PartidoEnVivoService
{
    /**
     * Obtiene todos los datos necesarios para la pantalla de registro en vivo.
     */
    public function obtenerDatosEnVivo(Partido $partido): array
    {
        $partido->load([
            'torneo',
            'jornada',
            'cancha',
            'arbitro',
            'equipoLocal.equipo.players',
            'equipoVisitante.equipo.players',
            'eventos.jugador',
            'eventos.jugadorRelacionado',
            'asistencias.jugador',
        ]);

        $jugadoresLocal = $this->mapearJugadores($partido->equipoLocal?->equipo?->players ?? collect());
        $jugadoresVisitante = $this->mapearJugadores($partido->equipoVisitante?->equipo?->players ?? collect());

        $eventos = $partido->eventos->map(fn ($e) => [
            'id' => $e->id,
            'tipo' => $e->tipo->value,
            'tipo_label' => $e->tipo->label(),
            'tipo_icon' => $e->tipo->icon(),
            'tipo_color' => $e->tipo->colorClass(),
            'minuto' => $e->minuto,
            'equipo_id' => $e->equipo_id,
            'jugador' => $e->jugador?->only(['id', 'nombre', 'numero']),
            'jugador_relacionado' => $e->jugadorRelacionado?->only(['id', 'nombre', 'numero']),
            'comentario' => $e->comentario,
            'created_at' => $e->created_at,
        ]);

        $asistencias = $partido->asistencias->keyBy('jugador_id');

        $faltasLocal = $this->contarFaltasEquipo($partido, $partido->equipo_local_id);
        $faltasVisitante = $this->contarFaltasEquipo($partido, $partido->equipo_visitante_id);

        return [
            'partido' => [
                'id' => $partido->id,
                'estado' => $partido->estado,
                'mitad' => $partido->mitad,
                'goles_local' => $partido->goles_local ?? 0,
                'goles_visitante' => $partido->goles_visitante ?? 0,
                'duracion_minutos' => $partido->duracion_minutos,
                'fecha' => $partido->fecha?->format('Y-m-d'),
                'hora' => $partido->hora,
                'torneo' => $partido->torneo?->only(['id', 'nombre', 'configuracion_asistencia_delegado']),
                'jornada' => $partido->jornada?->only(['id', 'nombre']),
                'cancha' => $partido->cancha?->only(['id', 'nombre', 'tipo']),
                'arbitro' => $partido->arbitro?->only(['id', 'nombre']),
                'equipo_local' => [
                    'id' => $partido->equipo_local_id,
                    'nombre' => $partido->equipoLocal?->equipo?->name ?? 'Local',
                    'shield' => $partido->equipoLocal?->equipo?->shield,
                    'jugadores' => $jugadoresLocal,
                ],
                'equipo_visitante' => [
                    'id' => $partido->equipo_visitante_id,
                    'nombre' => $partido->equipoVisitante?->equipo?->name ?? 'Visitante',
                    'shield' => $partido->equipoVisitante?->equipo?->shield,
                    'jugadores' => $jugadoresVisitante,
                ],
            ],
            'eventos' => $eventos,
            'asistencias' => $asistencias,
            'faltas_local' => $faltasLocal,
            'faltas_visitante' => $faltasVisitante,
            'alerta_penal_local' => $this->debeAlertarPenal($partido, $faltasLocal),
            'alerta_penal_visitante' => $this->debeAlertarPenal($partido, $faltasVisitante),
            'expulsados_local' => $this->obtenerExpulsados($partido, $partido->equipo_local_id),
            'expulsados_visitante' => $this->obtenerExpulsados($partido, $partido->equipo_visitante_id),
        ];
    }

    public function registrarEvento(Partido $partido, array $data): PartidoEvento
    {
        return DB::transaction(function () use ($partido, $data) {
            $evento = PartidoEvento::create([
                'tenant_id' => $partido->tenant_id,
                'partido_id' => $partido->id,
                'equipo_id' => $data['equipo_id'],
                'jugador_id' => $data['jugador_id'] ?? null,
                'jugador_relacionado_id' => $data['jugador_relacionado_id'] ?? null,
                'tipo' => $data['tipo'],
                'minuto' => $data['minuto'],
                'comentario' => $data['comentario'] ?? null,
            ]);

            // Si es tarjeta amarilla, verificar si ya tiene 2 en este partido -> roja automática
            if ($evento->tipo === PartidoEventoTipoEnum::TARJETA_AMARILLA
                && $evento->jugador_id
                && $this->contarAmarillasJugador($partido, $evento->jugador_id) >= 2) {
                PartidoEvento::create([
                    'tenant_id' => $partido->tenant_id,
                    'partido_id' => $partido->id,
                    'equipo_id' => $data['equipo_id'],
                    'jugador_id' => $evento->jugador_id,
                    'tipo' => PartidoEventoTipoEnum::TARJETA_ROJA,
                    'minuto' => $data['minuto'],
                    'comentario' => 'Segunda tarjeta amarilla',
                ]);
            }

            $this->recalcularGoles($partido);

            return $evento;
        });
    }

    public function eliminarEvento(PartidoEvento $evento): void
    {
        DB::transaction(function () use ($evento) {
            $partido = $evento->partido;
            $evento->delete();
            $this->recalcularGoles($partido);
        });
    }

    public function registrarAsistencias(Partido $partido, array $lista): void
    {
        DB::transaction(function () use ($partido, $lista) {
            foreach ($lista as $item) {
                PartidoAsistencia::updateOrCreate(
                    [
                        'tenant_id' => $partido->tenant_id,
                        'partido_id' => $partido->id,
                        'jugador_id' => $item['jugador_id'],
                    ],
                    [
                        'equipo_id' => $item['equipo_id'],
                        'asistio_primera_mitad' => $item['asistio_primera_mitad'] ?? null,
                        'asistio_segunda_mitad' => $item['asistio_segunda_mitad'] ?? null,
                    ]
                );
            }
        });
    }

    public function cambiarEstado(Partido $partido, string $nuevoEstado): void
    {
        $permitidas = [
            'programado' => ['en_juego', 'cancelado'],
            'en_juego' => ['descanso', 'finalizado', 'suspendido'],
            'descanso' => ['en_juego'],
        ];

        if (! in_array($nuevoEstado, $permitidas[$partido->estado] ?? [], true)) {
            throw new \InvalidArgumentException(
                "Transición de estado no permitida: {$partido->estado} → {$nuevoEstado}"
            );
        }

        if ($nuevoEstado === 'en_juego' && $partido->estado === 'programado') {
            $partido->mitad = 1;
        }

        if ($nuevoEstado === 'en_juego' && $partido->estado === 'descanso') {
            $partido->mitad = 2;
        }

        if ($nuevoEstado === 'finalizado') {
            $this->recalcularGoles($partido);
        }

        $partido->estado = $nuevoEstado;
        $partido->save();
    }

    private function recalcularGoles(Partido $partido): void
    {
        $localId = $partido->equipo_local_id;
        $visitanteId = $partido->equipo_visitante_id;

        $golesLocal = PartidoEvento::where('partido_id', $partido->id)
            ->where(function ($q) use ($localId, $visitanteId) {
                $q->where(function ($sq) use ($localId) {
                    $sq->where('equipo_id', $localId)
                        ->whereIn('tipo', [PartidoEventoTipoEnum::GOL->value, PartidoEventoTipoEnum::GOL_PENAL->value]);
                })->orWhere(function ($sq) use ($visitanteId) {
                    $sq->where('equipo_id', $visitanteId)
                        ->where('tipo', PartidoEventoTipoEnum::AUTOGOL->value);
                });
            })
            ->count();

        $golesVisitante = PartidoEvento::where('partido_id', $partido->id)
            ->where(function ($q) use ($localId, $visitanteId) {
                $q->where(function ($sq) use ($visitanteId) {
                    $sq->where('equipo_id', $visitanteId)
                        ->whereIn('tipo', [PartidoEventoTipoEnum::GOL->value, PartidoEventoTipoEnum::GOL_PENAL->value]);
                })->orWhere(function ($sq) use ($localId) {
                    $sq->where('equipo_id', $localId)
                        ->where('tipo', PartidoEventoTipoEnum::AUTOGOL->value);
                });
            })
            ->count();

        $partido->goles_local = $golesLocal;
        $partido->goles_visitante = $golesVisitante;
        $partido->save();
    }

    private function contarAmarillasJugador(Partido $partido, int $jugadorId): int
    {
        return PartidoEvento::where('partido_id', $partido->id)
            ->where('jugador_id', $jugadorId)
            ->where('tipo', PartidoEventoTipoEnum::TARJETA_AMARILLA)
            ->count();
    }

    private function contarFaltasEquipo(Partido $partido, int $equipoId): int
    {
        return PartidoEvento::where('partido_id', $partido->id)
            ->where('equipo_id', $equipoId)
            ->whereIn('tipo', [
                PartidoEventoTipoEnum::TARJETA_AMARILLA->value,
                PartidoEventoTipoEnum::TARJETA_ROJA->value,
                PartidoEventoTipoEnum::FALTA->value,
            ])
            ->count();
    }

    private function debeAlertarPenal(Partido $partido, int $faltas): bool
    {
        $esFut7 = $partido->cancha?->tipo === 'futbol-7' || $partido->duracion_minutos <= 50;
        return $esFut7 && $faltas >= 5;
    }

    private function obtenerExpulsados(Partido $partido, int $equipoId): array
    {
        return PartidoEvento::where('partido_id', $partido->id)
            ->where('equipo_id', $equipoId)
            ->where('tipo', PartidoEventoTipoEnum::TARJETA_ROJA)
            ->pluck('jugador_id')
            ->filter()
            ->values()
            ->toArray();
    }

    private function mapearJugadores($players): array
    {
        return $players->map(fn ($p) => [
            'id' => $p->id,
            'nombre' => $p->nombre,
            'numero' => $p->numero,
            'posicion' => $p->posicion,
            'foto_url' => $p->foto_url,
        ])->values()->toArray();
    }
}
