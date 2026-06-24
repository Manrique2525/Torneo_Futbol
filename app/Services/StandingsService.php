<?php

namespace App\Services;

use App\Enums\PartidoEventoTipoEnum;
use App\Models\Partido;
use App\Models\PartidoEvento;
use App\Models\Torneo;
use App\Models\TorneoEquipo;
use App\Models\TorneoStanding;
use Illuminate\Support\Facades\DB;

class StandingsService
{
    /**
     * Recalcula toda la tabla de posiciones de un torneo y guarda el snapshot.
     */
    public function recalcular(Torneo $torneo): void
    {
        $esLiga = $torneo->tipo === 'liga';

        DB::transaction(function () use ($torneo, $esLiga) {
            // 1. Limpiar standings actuales del torneo
            TorneoStanding::where('torneo_id', $torneo->id)->delete();

            // 2. Obtener equipos aprobados del torneo
            $equipos = TorneoEquipo::with('equipo')
                ->where('torneo_id', $torneo->id)
                ->where('estado', 'aprobado')
                ->get()
                ->keyBy('id');

            if ($equipos->isEmpty()) {
                return;
            }

            // 3. Inicializar stats por equipo
            $stats = [];
            foreach ($equipos as $te) {
                $stats[$te->id] = [
                    'torneo_equipo_id' => $te->id,
                    'torneo_grupo_id' => $te->torneo_grupo_id,
                    'pj' => 0, 'pg' => 0, 'pe' => 0, 'pp' => 0,
                    'gf' => 0, 'gc' => 0, 'dg' => 0, 'pts' => 0,
                    'fair_play' => (float) $te->fair_play_points,
                ];
            }

            // 4. Obtener partidos finalizados del torneo
            $partidos = Partido::where('torneo_id', $torneo->id)
                ->where('estado', 'finalizado')
                ->get();

            foreach ($partidos as $partido) {
                $this->procesarPartido($partido, $stats, $equipos);
            }

            // 5. Guardar filas
            foreach ($stats as $row) {
                $row['dg'] = $row['gf'] - $row['gc'];
                if (! $esLiga) {
                    $row['pts'] = 0;
                }

                TorneoStanding::create([
                    'tenant_id' => $torneo->tenant_id,
                    'torneo_id' => $torneo->id,
                    'torneo_grupo_id' => $row['torneo_grupo_id'],
                    'torneo_equipo_id' => $row['torneo_equipo_id'],
                    'pj' => $row['pj'],
                    'pg' => $row['pg'],
                    'pe' => $row['pe'],
                    'pp' => $row['pp'],
                    'gf' => $row['gf'],
                    'gc' => $row['gc'],
                    'dg' => $row['dg'],
                    'pts' => $row['pts'],
                    'fair_play' => $row['fair_play'],
                    'posicion_posiciones' => null,
                    'posicion_rendimiento' => null,
                ]);
            }

            // 6. Calcular AMBAS posiciones por grupo
            $gruposIds = $equipos->pluck('torneo_grupo_id')->unique()->filter();
            if ($gruposIds->isEmpty()) {
                $gruposIds = [null];
            }

            foreach ($gruposIds as $grupoId) {
                $query = TorneoStanding::where('torneo_id', $torneo->id);
                if ($grupoId !== null) {
                    $query->where('torneo_grupo_id', $grupoId);
                } else {
                    $query->whereNull('torneo_grupo_id');
                }

                // Posición por "Posiciones" (Pts → DG → GF → FP)
                $filasPosiciones = (clone $query)
                    ->orderByDesc('pts')
                    ->orderByDesc('dg')
                    ->orderByDesc('gf')
                    ->orderByDesc('fair_play')
                    ->get();

                $pos = 1;
                foreach ($filasPosiciones as $fila) {
                    $fila->update(['posicion_posiciones' => $pos]);
                    $pos++;
                }

                // Posición por "Rendimiento" (PG → DG → GF → FP)
                $filasRendimiento = (clone $query)
                    ->orderByDesc('pg')
                    ->orderByDesc('dg')
                    ->orderByDesc('gf')
                    ->orderByDesc('fair_play')
                    ->get();

                $pos = 1;
                foreach ($filasRendimiento as $fila) {
                    $fila->update(['posicion_rendimiento' => $pos]);
                    $pos++;
                }
            }
        });
    }

    /**
     * Aplica descuento de fair play por evento si el torneo lo tiene activado.
     */
    public function aplicarDescuentoFairPlay(PartidoEvento $evento): void
    {
        $partido = $evento->partido;
        if (! $partido || ! $partido->torneo?->fair_play_automatico) {
            return;
        }

        $descuento = match ($evento->tipo) {
            PartidoEventoTipoEnum::TARJETA_AMARILLA => 0.5,
            PartidoEventoTipoEnum::TARJETA_ROJA => 1.5,
            PartidoEventoTipoEnum::FALTA => 0.1,
            default => 0,
        };

        if ($descuento <= 0) {
            return;
        }

        $torneoEquipo = TorneoEquipo::where('torneo_id', $partido->torneo_id)
            ->where('team_id', $evento->equipo_id)
            ->first();

        if ($torneoEquipo) {
            $nuevo = max(0, (float) $torneoEquipo->fair_play_points - $descuento);
            $torneoEquipo->update(['fair_play_points' => $nuevo]);
        }
    }

    private function procesarPartido(Partido $partido, array &$stats, $equipos): void
    {
        $localId = $partido->equipo_local_id;
        $visitanteId = $partido->equipo_visitante_id;
        $golesLocal = (int) ($partido->goles_local ?? 0);
        $golesVisitante = (int) ($partido->goles_visitante ?? 0);

        // Buscar los torneo_equipo_id correspondientes
        $teLocal = $equipos->first(fn ($e) => $e->id === $localId);
        $teVisitante = $equipos->first(fn ($e) => $e->id === $visitanteId);

        if (! $teLocal || ! $teVisitante) {
            return;
        }

        // Local
        $stats[$teLocal->id]['pj'] += 1;
        $stats[$teLocal->id]['gf'] += $golesLocal;
        $stats[$teLocal->id]['gc'] += $golesVisitante;

        if ($golesLocal > $golesVisitante) {
            $stats[$teLocal->id]['pg'] += 1;
            $stats[$teLocal->id]['pts'] += 3;
        } elseif ($golesLocal === $golesVisitante) {
            $stats[$teLocal->id]['pe'] += 1;
            $stats[$teLocal->id]['pts'] += 1;
        } else {
            $stats[$teLocal->id]['pp'] += 1;
        }

        // Visitante
        $stats[$teVisitante->id]['pj'] += 1;
        $stats[$teVisitante->id]['gf'] += $golesVisitante;
        $stats[$teVisitante->id]['gc'] += $golesLocal;

        if ($golesVisitante > $golesLocal) {
            $stats[$teVisitante->id]['pg'] += 1;
            $stats[$teVisitante->id]['pts'] += 3;
        } elseif ($golesVisitante === $golesLocal) {
            $stats[$teVisitante->id]['pe'] += 1;
            $stats[$teVisitante->id]['pts'] += 1;
        } else {
            $stats[$teVisitante->id]['pp'] += 1;
        }
    }
}
