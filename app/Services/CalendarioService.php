<?php

namespace App\Services;

use App\Models\Cancha;
use App\Models\Jornada;
use App\Models\Partido;
use App\Models\Torneo;
use App\Models\TorneoGrupo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CalendarioService
{
    protected RoundRobinGenerator $roundRobin;

    protected BracketGenerator $bracket;

    protected MatchSchedulingService $schedulingService;

    public function __construct(
        RoundRobinGenerator $roundRobin,
        BracketGenerator $bracket,
        MatchSchedulingService $schedulingService
    ) {
        $this->roundRobin = $roundRobin;
        $this->bracket = $bracket;
        $this->schedulingService = $schedulingService;
    }

    public function preview(Torneo $torneo): array
    {
        $this->validarTorneo($torneo);

        $equipos = $torneo->inscripciones()
            ->aprobados()
            ->with('equipo')
            ->get()
            ->keyBy('id')
            ->toArray();

        if (count($equipos) < 4) {
            throw ValidationException::withMessages([
                'equipos' => 'Se requieren mínimo 4 equipos aprobados para generar el calendario.',
            ]);
        }

        $jornadasRegulares = [];
        $bracket = [];

        if ($torneo->esRelampago() && $torneo->formato_relampago === 'eliminacion_directa') {
            $bracket = $this->generarBracketPreview($torneo, $equipos);
        } else {
            $jornadasRegulares = $this->generarFaseRegularPreview($torneo, $equipos);

            if ($torneo->tienePlayoff()) {
                $bracket = $this->generarBracketPreview($torneo, $equipos);
            }
        }

        $fechaInicio = $torneo->fecha_inicio ? Carbon::parse($torneo->fecha_inicio) : Carbon::now();
        $jornadasConFechas = $this->asignarFechas($jornadasRegulares, $fechaInicio, $torneo);

        return [
            'torneo' => $torneo->only([
                'id', 'nombre', 'tipo', 'ida_y_vuelta', 'tiene_playoff',
                'playoff_equipos', 'playoff_ida_vuelta', 'formato_relampago',
                'hora_inicio', 'duracion_minutos',
            ]),
            'total_equipos' => count($equipos),
            'jornadas' => $jornadasConFechas,
            'bracket' => $bracket,
            'canchas_disponibles' => $this->obtenerCanchasDisponibles($torneo),
        ];
    }

    public function confirmar(Torneo $torneo, array $ajustes): void
    {
        $preview = $this->preview($torneo);
        $this->validarCanchas($torneo, $ajustes, $preview);

        DB::transaction(function () use ($torneo, $ajustes, $preview) {

            foreach ($preview['jornadas'] as $jornadaData) {
                $ajusteJornada = $ajustes['jornadas'][$jornadaData['numero']] ?? [];

                $jornada = Jornada::create([
                    'torneo_id' => $torneo->id,
                    'nombre' => $ajusteJornada['nombre'] ?? 'Jornada '.$jornadaData['numero'],
                    'numero' => $jornadaData['numero'],
                    'fecha_inicio' => $ajusteJornada['fecha'] ?? $jornadaData['fecha'],
                    'fecha_fin' => $ajusteJornada['fecha'] ?? $jornadaData['fecha'],
                    'estado' => 'borrador',
                ]);

                foreach ($jornadaData['partidos'] as $idx => $partidoData) {
                    $ajustePartido = $ajustes['partidos'][$jornadaData['numero']][$idx] ?? [];

                    Partido::create([
                        'torneo_id' => $torneo->id,
                        'jornada_id' => $jornada->id,
                        'equipo_local_id' => $partidoData['local']['id'],
                        'equipo_visitante_id' => $partidoData['visitante']['id'],
                        'cancha_id' => $ajustePartido['cancha_id'] ?? null,
                        'arbitro_id' => $ajustePartido['arbitro_id'] ?? null,
                        'fecha' => $ajustePartido['fecha'] ?? $jornadaData['fecha'],
                        'hora' => $ajustePartido['hora'] ?? $torneo->hora_inicio,
                        'duracion_minutos' => $ajustePartido['duracion_minutos'] ?? $torneo->duracion_minutos,
                        'estado' => 'programado',
                        'fase' => 'regular',
                        'es_vuelta' => $partidoData['es_vuelta'] ?? false,
                    ]);
                }
            }

            foreach ($preview['bracket'] as $faseData) {
                foreach ($faseData['partidos'] as $idx => $partidoData) {
                    $ajustePartido = $ajustes['bracket'][$faseData['fase']][$idx] ?? [];

                    Partido::create([
                        'torneo_id' => $torneo->id,
                        'jornada_id' => null,
                        'equipo_local_id' => $partidoData['local']['id'] ?? null,
                        'equipo_visitante_id' => $partidoData['visitante']['id'] ?? null,
                        'cancha_id' => $ajustePartido['cancha_id'] ?? null,
                        'arbitro_id' => $ajustePartido['arbitro_id'] ?? null,
                        'fecha' => $ajustePartido['fecha'] ?? null,
                        'hora' => $ajustePartido['hora'] ?? $torneo->hora_inicio,
                        'duracion_minutos' => $ajustePartido['duracion_minutos'] ?? $torneo->duracion_minutos,
                        'estado' => 'programado',
                        'fase' => $partidoData['fase'],
                        'es_vuelta' => $partidoData['es_vuelta'] ?? false,
                        'llave_bracket' => $partidoData['llave'],
                        'orden_bracket' => $partidoData['orden'],
                    ]);
                }
            }
        });
    }

    public function eliminar(Torneo $torneo): void
    {
        $partidosFinalizados = $torneo->partidos()
            ->where('estado', 'finalizado')
            ->exists();

        if ($partidosFinalizados) {
            throw ValidationException::withMessages([
                'torneo' => 'No se puede eliminar el calendario porque hay partidos finalizados.',
            ]);
        }

        DB::transaction(function () use ($torneo) {
            $torneo->partidos()->delete();
            $torneo->jornadas()->delete();
        });
    }

    protected function validarTorneo(Torneo $torneo): void
    {
        if (in_array($torneo->estado, ['finalizado', 'cancelado'], true)) {
            throw ValidationException::withMessages([
                'torneo' => 'El torneo no permite generar calendario (estado: '.$torneo->estado.').',
            ]);
        }

        if ($torneo->tiene_playoff && $torneo->playoff_equipos) {
            if (! Torneo::esPotenciaDe2($torneo->playoff_equipos)) {
                throw ValidationException::withMessages([
                    'playoff_equipos' => 'El número de equipos en playoff debe ser potencia de 2 (2, 4, 8, 16).',
                ]);
            }
        }
    }

    protected function validarCanchas(Torneo $torneo, array $ajustes, array $preview): void
    {
        $horaDefecto = $torneo->hora_inicio instanceof Carbon
            ? $torneo->hora_inicio->format('H:i')
            : (string) $torneo->hora_inicio;

        $slots = [];

        foreach ($preview['jornadas'] as $jornadaData) {
            $ajusteJornada = $ajustes['jornadas'][$jornadaData['numero']] ?? [];
            $fechaJornada = $ajusteJornada['fecha'] ?? $jornadaData['fecha'];

            foreach ($jornadaData['partidos'] as $idx => $partidoData) {
                $ajuste = $ajustes['partidos'][$jornadaData['numero']][$idx] ?? [];
                $canchaId = $ajuste['cancha_id'] ?? null;

                if ($canchaId) {
                    $slots[] = [
                        'cancha_id' => (int) $canchaId,
                        'fecha' => $ajuste['fecha'] ?? $fechaJornada,
                        'hora' => $ajuste['hora'] ?? $horaDefecto,
                        'duracion_minutos' => (int) ($ajuste['duracion_minutos'] ?? $torneo->duracion_minutos),
                    ];
                }
            }
        }

        foreach ($preview['bracket'] as $faseData) {
            foreach ($faseData['partidos'] as $idx => $partidoData) {
                $ajuste = $ajustes['bracket'][$faseData['fase']][$idx] ?? [];
                $canchaId = $ajuste['cancha_id'] ?? null;

                if ($canchaId) {
                    $slots[] = [
                        'cancha_id' => (int) $canchaId,
                        'fecha' => $ajuste['fecha'] ?? null,
                        'hora' => $ajuste['hora'] ?? $horaDefecto,
                        'duracion_minutos' => (int) ($ajuste['duracion_minutos'] ?? $torneo->duracion_minutos),
                    ];
                }
            }
        }

        foreach ($slots as $i => $slot) {
            $fechaStr = $slot['fecha'] instanceof Carbon
                ? $slot['fecha']->format('Y-m-d')
                : (string) $slot['fecha'];
            $horaStr = $slot['hora'] instanceof Carbon
                ? $slot['hora']->format('H:i')
                : (string) $slot['hora'];

            $this->schedulingService->assertCanchaDisponible(
                $slot['cancha_id'],
                $fechaStr,
                $horaStr,
                $slot['duracion_minutos']
            );

            $this->schedulingService->assertSinConflictoCancha(
                $slot['cancha_id'],
                $fechaStr,
                $horaStr,
                $slot['duracion_minutos']
            );

            $inicioA = Carbon::parse($fechaStr.' '.$horaStr);
            $finA = $inicioA->copy()->addMinutes($slot['duracion_minutos']);

            for ($j = $i + 1; $j < count($slots); $j++) {
                $other = $slots[$j];

                if ($slot['cancha_id'] !== $other['cancha_id']) {
                    continue;
                }

                $otherFechaStr = $other['fecha'] instanceof Carbon
                    ? $other['fecha']->format('Y-m-d')
                    : (string) $other['fecha'];
                $otherHoraStr = $other['hora'] instanceof Carbon
                    ? $other['hora']->format('H:i')
                    : (string) $other['hora'];

                if ($fechaStr !== $otherFechaStr) {
                    continue;
                }

                $inicioB = Carbon::parse($otherFechaStr.' '.$otherHoraStr);
                $finB = $inicioB->copy()->addMinutes($other['duracion_minutos']);

                if ($inicioA->lt($finB) && $finA->gt($inicioB)) {
                    throw ValidationException::withMessages([
                        'cancha_id' => 'Conflicto de horario: dos partidos asignados a la misma cancha al mismo tiempo.',
                    ]);
                }
            }
        }
    }

    protected function generarFaseRegularPreview(Torneo $torneo, array $equipos): array
    {
        $idaYVuelta = $torneo->tieneIdaYVuelta();

        if ($torneo->esRelampago() && $torneo->formato_relampago === 'grupos') {
            $grupos = TorneoGrupo::where('torneo_id', $torneo->id)
                ->orderBy('orden')
                ->orderBy('nombre')
                ->get();

            $equiposPorGrupo = [];

            foreach ($grupos as $grupo) {
                $equiposGrupo = collect($equipos)
                    ->filter(fn ($e) => ($e['torneo_grupo_id'] ?? null) === $grupo->id)
                    ->toArray();

                if (count($equiposGrupo) >= 2) {
                    $equiposPorGrupo[$grupo->id] = $equiposGrupo;
                }
            }

            if (empty($equiposPorGrupo)) {
                return $this->roundRobin->generar($equipos, $idaYVuelta);
            }

            return $this->roundRobin->generarPorGrupos($equiposPorGrupo, $idaYVuelta);
        }

        return $this->roundRobin->generar($equipos, $idaYVuelta);
    }

    protected function generarBracketPreview(Torneo $torneo, array $equipos): array
    {
        $numEquipos = $torneo->playoff_equipos ?? min(count($equipos), 8);

        if (! Torneo::esPotenciaDe2($numEquipos)) {
            $numEquipos = $this->potenciaDe2Inferior($numEquipos);
        }

        if ($numEquipos < 2) {
            return [];
        }

        $equiposClasificados = array_slice(array_values($equipos), 0, $numEquipos);

        $idaYVuelta = $torneo->playoff_ida_vuelta ?? false;

        if ($torneo->esRelampago()) {
            $idaYVuelta = false;
        }

        return $this->bracket->generar($equiposClasificados, $idaYVuelta);
    }

    protected function asignarFechas(array $jornadas, Carbon $fechaInicio, Torneo $torneo): array
    {
        $fechaActual = $fechaInicio->copy();

        foreach ($jornadas as &$jornada) {
            while ($fechaActual->dayOfWeek === Carbon::FRIDAY) {
                $fechaActual->addDay();
            }

            $jornada['fecha'] = $fechaActual->format('Y-m-d');
            $jornada['dia_semana'] = $fechaActual->locale('es')->dayName;

            $fechaActual->addWeek();
        }

        return $jornadas;
    }

    protected function obtenerCanchasDisponibles(Torneo $torneo): array
    {
        return Cancha::where('tenant_id', $torneo->tenant_id)
            ->where('estado', 'activo')
            ->with('disponibilidades')
            ->get()
            ->map(fn ($c) => [
                'id' => $c->id,
                'nombre' => $c->nombre,
                'tipo' => $c->tipo,
                'disponibilidades' => $c->disponibilidades->map(fn ($d) => [
                    'dia_semana' => $d->dia_semana,
                    'hora_inicio' => $d->hora_inicio,
                    'hora_fin' => $d->hora_fin,
                ]),
            ])
            ->toArray();
    }

    protected function potenciaDe2Inferior(int $n): int
    {
        $potencia = 1;

        while ($potencia * 2 <= $n) {
            $potencia *= 2;
        }

        return $potencia;
    }
}
