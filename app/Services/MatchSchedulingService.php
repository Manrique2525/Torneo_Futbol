<?php

namespace App\Services;

use App\Models\Arbitro;
use App\Models\DisponibilidadCancha;
use App\Models\Jornada;
use App\Models\Partido;
use App\Models\Torneo;
use App\Models\TorneoEquipo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MatchSchedulingService
{
    public function programar(array $data): Partido
    {
        return DB::transaction(function () use ($data) {
            $tenantId = auth()->user()->tenant_id;

            $this->assertTorneoActivo($data['torneo_id'], $tenantId);
            $this->assertEquiposAprobados($data['equipo_local_id'], $data['equipo_visitante_id'], $data['torneo_id']);
            $this->assertEquiposDistintos($data['equipo_local_id'], $data['equipo_visitante_id']);
            $this->assertSinRepeticionExcesiva(
                $data['torneo_id'],
                $data['equipo_local_id'],
                $data['equipo_visitante_id'],
                $data['fase'] ?? 'regular'
            );

            if (! empty($data['jornada_id'])) {
                $this->assertJornadaDelMismoTorneo((int) $data['jornada_id'], (int) $data['torneo_id']);
            }

            if (! empty($data['cancha_id'])) {
                $this->assertCanchaDisponible(
                    (int) $data['cancha_id'],
                    $data['fecha'],
                    $data['hora'],
                    (int) ($data['duracion_minutos'] ?? 90)
                );

                $this->assertSinConflictoCancha(
                    (int) $data['cancha_id'],
                    $data['fecha'],
                    $data['hora'],
                    (int) ($data['duracion_minutos'] ?? 90)
                );
            }

            if (! empty($data['arbitro_id'])) {
                $this->assertArbitroDisponible(
                    (int) $data['arbitro_id'],
                    $tenantId,
                    $data['fecha'],
                    $data['hora'],
                    (int) ($data['duracion_minutos'] ?? 90)
                );
            }

            return Partido::create($data);
        });
    }

    public function actualizar(Partido $partido, array $data): Partido
    {
        return DB::transaction(function () use ($partido, $data) {
            $tenantId = auth()->user()->tenant_id;

            $this->assertTorneoActivo($data['torneo_id'], $tenantId);
            $this->assertEquiposAprobados($data['equipo_local_id'], $data['equipo_visitante_id'], $data['torneo_id']);
            $this->assertEquiposDistintos($data['equipo_local_id'], $data['equipo_visitante_id']);
            $this->assertSinRepeticionExcesiva(
                $data['torneo_id'],
                $data['equipo_local_id'],
                $data['equipo_visitante_id'],
                $data['fase'] ?? $partido->fase ?? 'regular',
                $partido->id
            );

            if (! empty($data['jornada_id'])) {
                $this->assertJornadaDelMismoTorneo((int) $data['jornada_id'], (int) $data['torneo_id']);
            }

            if (! empty($data['cancha_id'])) {
                $this->assertCanchaDisponible(
                    (int) $data['cancha_id'],
                    $data['fecha'],
                    $data['hora'],
                    (int) ($data['duracion_minutos'] ?? 90)
                );

                $this->assertSinConflictoCancha(
                    (int) $data['cancha_id'],
                    $data['fecha'],
                    $data['hora'],
                    (int) ($data['duracion_minutos'] ?? 90),
                    $partido->id
                );
            }

            if (! empty($data['arbitro_id'])) {
                $this->assertArbitroDisponible(
                    (int) $data['arbitro_id'],
                    $tenantId,
                    $data['fecha'],
                    $data['hora'],
                    (int) ($data['duracion_minutos'] ?? 90),
                    $partido->id
                );
            }

            $partido->update($data);

            return $partido->fresh();
        });
    }

    // ── Assertions ──────────────────────────────────

    protected function assertTorneoActivo(int $torneoId, int $tenantId): void
    {
        $torneo = Torneo::query()
            ->withoutGlobalScopes()
            ->where('id', $torneoId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (! $torneo) {
            throw ValidationException::withMessages([
                'torneo_id' => 'El torneo seleccionado no es válido.',
            ]);
        }

        if (in_array($torneo->estado, ['finalizado', 'cancelado'], true)) {
            throw ValidationException::withMessages([
                'torneo_id' => 'El torneo no permite programar partidos (estado: '.$torneo->estado.').',
            ]);
        }
    }

    protected function assertEquiposAprobados(int $localId, int $visitanteId, int $torneoId): void
    {
        $local = TorneoEquipo::query()
            ->withoutGlobalScopes()
            ->where('id', $localId)
            ->where('torneo_id', $torneoId)
            ->where('estado', 'aprobado')
            ->first();

        if (! $local) {
            throw ValidationException::withMessages([
                'equipo_local_id' => 'El equipo local no está aprobado en este torneo.',
            ]);
        }

        $visitante = TorneoEquipo::query()
            ->withoutGlobalScopes()
            ->where('id', $visitanteId)
            ->where('torneo_id', $torneoId)
            ->where('estado', 'aprobado')
            ->first();

        if (! $visitante) {
            throw ValidationException::withMessages([
                'equipo_visitante_id' => 'El equipo visitante no está aprobado en este torneo.',
            ]);
        }
    }

    protected function assertEquiposDistintos(int $localId, int $visitanteId): void
    {
        if ($localId === $visitanteId) {
            throw ValidationException::withMessages([
                'equipo_visitante_id' => 'El equipo local y visitante no pueden ser el mismo.',
            ]);
        }
    }

    protected function assertJornadaDelMismoTorneo(int $jornadaId, int $torneoId): void
    {
        $jornada = Jornada::query()
            ->withoutGlobalScopes()
            ->where('id', $jornadaId)
            ->where('torneo_id', $torneoId)
            ->first();

        if (! $jornada) {
            throw ValidationException::withMessages([
                'jornada_id' => 'La jornada seleccionada no pertenece al torneo.',
            ]);
        }
    }

    protected function assertCanchaDisponible(int $canchaId, string $fecha, string $hora, int $duracionMinutos): void
    {
        $diaSemana = Carbon::parse($fecha)->dayOfWeek;

        $disponibilidad = DisponibilidadCancha::query()
            ->withoutGlobalScopes()
            ->where('cancha_id', $canchaId)
            ->where('dia_semana', $diaSemana)
            ->first();

        if (! $disponibilidad) {
            throw ValidationException::withMessages([
                'cancha_id' => 'La cancha no tiene disponibilidad para el día seleccionado.',
            ]);
        }

        $horaInicio = Carbon::createFromTimeString($hora);
        $horaFin = $horaInicio->copy()->addMinutes($duracionMinutos);

        $dispInicio = Carbon::createFromTimeString((string) $disponibilidad->hora_inicio);
        $dispFin = Carbon::createFromTimeString((string) $disponibilidad->hora_fin);

        if ($horaInicio->lt($dispInicio) || $horaFin->gt($dispFin)) {
            throw ValidationException::withMessages([
                'cancha_id' => 'El partido debe programarse dentro del rango '.
                    $dispInicio->format('H:i').' - '.$dispFin->format('H:i').'.',
            ]);
        }
    }

    protected function assertSinConflictoCancha(
        int $canchaId,
        string $fecha,
        string $hora,
        int $duracionMinutos,
        ?int $exceptPartidoId = null
    ): void {
        $nuevoInicio = Carbon::parse($fecha.' '.$hora);
        $nuevoFin = $nuevoInicio->copy()->addMinutes($duracionMinutos);

        $partidosConflicto = Partido::query()
            ->withoutGlobalScopes()
            ->where('cancha_id', $canchaId)
            ->where('fecha', $fecha)
            ->whereNotIn('estado', ['cancelado', 'suspendido'])
            ->when($exceptPartidoId, fn ($q) => $q->where('id', '!=', $exceptPartidoId))
            ->get();

        foreach ($partidosConflicto as $partido) {
            $existenteInicio = Carbon::parse($partido->fecha.' '.$partido->hora);
            $existenteFin = $existenteInicio->copy()->addMinutes($partido->duracion_minutos ?? 90);

            if ($nuevoInicio->lt($existenteFin) && $nuevoFin->gt($existenteInicio)) {
                throw ValidationException::withMessages([
                    'cancha_id' => 'La cancha ya está ocupada en ese horario (conflicto con el partido #'.$partido->id.': '.
                        $existenteInicio->format('H:i').' - '.$existenteFin->format('H:i').').',
                ]);
            }
        }
    }

    protected function assertArbitroDisponible(
        int $arbitroId,
        int $tenantId,
        string $fecha,
        string $hora,
        int $duracionMinutos,
        ?int $exceptPartidoId = null
    ): void {
        $arbitro = Arbitro::where('id', $arbitroId)
            ->where('tenant_id', $tenantId)
            ->first();

        if (! $arbitro) {
            throw ValidationException::withMessages([
                'arbitro_id' => 'El árbitro seleccionado no es válido.',
            ]);
        }

        if (! $arbitro->disponible) {
            throw ValidationException::withMessages([
                'arbitro_id' => 'El árbitro no está disponible.',
            ]);
        }

        $nuevoInicio = Carbon::parse($fecha.' '.$hora);
        $nuevoFin = $nuevoInicio->copy()->addMinutes($duracionMinutos);

        $partidosConflicto = Partido::query()
            ->withoutGlobalScopes()
            ->where('arbitro_id', $arbitroId)
            ->where('fecha', $fecha)
            ->whereNotIn('estado', ['cancelado', 'suspendido'])
            ->when($exceptPartidoId, fn ($q) => $q->where('id', '!=', $exceptPartidoId))
            ->get();

        foreach ($partidosConflicto as $partido) {
            $existenteInicio = Carbon::parse($partido->fecha.' '.$partido->hora);
            $existenteFin = $existenteInicio->copy()->addMinutes($partido->duracion_minutos ?? 90);

            if ($nuevoInicio->lt($existenteFin) && $nuevoFin->gt($existenteInicio)) {
                throw ValidationException::withMessages([
                    'arbitro_id' => 'El árbitro ya está asignado en ese horario (conflicto con el partido #'.$partido->id.': '.
                        $existenteInicio->format('H:i').' - '.$existenteFin->format('H:i').').',
                ]);
            }
        }
    }

    protected function assertSinRepeticionExcesiva(
        int $torneoId,
        int $localId,
        int $visitanteId,
        string $fase = 'regular',
        ?int $exceptPartidoId = null
    ): void {
        if ($fase !== 'regular') {
            return;
        }

        $torneo = Torneo::query()
            ->withoutGlobalScopes()
            ->where('id', $torneoId)
            ->first();

        if (! $torneo) {
            return;
        }

        $maxEnfrentamientos = $torneo->ida_y_vuelta ? 2 : 1;

        $enfrentamientos = Partido::query()
            ->withoutGlobalScopes()
            ->where('torneo_id', $torneoId)
            ->where('fase', 'regular')
            ->whereNotIn('estado', ['cancelado'])
            ->when($exceptPartidoId, fn ($q) => $q->where('id', '!=', $exceptPartidoId))
            ->where(function ($q) use ($localId, $visitanteId) {
                $q->where(function ($q2) use ($localId, $visitanteId) {
                    $q2->where('equipo_local_id', $localId)
                        ->where('equipo_visitante_id', $visitanteId);
                })->orWhere(function ($q2) use ($localId, $visitanteId) {
                    $q2->where('equipo_local_id', $visitanteId)
                        ->where('equipo_visitante_id', $localId);
                });
            })
            ->count();

        if ($enfrentamientos >= $maxEnfrentamientos) {
            $mensaje = $torneo->ida_y_vuelta
                ? 'Ya se han programado los 2 enfrentamientos (ida y vuelta) entre estos equipos en la fase regular.'
                : 'Ya existe un enfrentamiento entre estos equipos en la fase regular. El torneo no permite ida y vuelta.';

            throw ValidationException::withMessages([
                'equipo_visitante_id' => $mensaje,
            ]);
        }
    }
}
