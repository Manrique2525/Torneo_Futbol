<?php

namespace App\Services;

class RoundRobinGenerator
{
    public function generar(array $equipos, bool $idaYVuelta): array
    {
        $n = count($equipos);

        if ($n < 2) {
            return [];
        }

        $tieneBye = $n % 2 !== 0;

        if ($tieneBye) {
            $equipos[] = null;
            $n++;
        }

        $jornadas = [];
        $numJornadas = $n - 1;

        $ids = array_keys($equipos);
        $fixed = $ids[0];
        $rotating = array_slice($ids, 1);

        for ($j = 0; $j < $numJornadas; $j++) {
            $partidos = [];
            $current = array_merge([$fixed], $rotating);

            for ($i = 0; $i < $n / 2; $i++) {
                $local = $current[$i];
                $visitante = $current[$n - 1 - $i];

                $equipoLocal = $equipos[$local] ?? null;
                $equipoVisitante = $equipos[$visitante] ?? null;

                if ($equipoLocal === null || $equipoVisitante === null) {
                    continue;
                }

                if ($j % 2 === 0) {
                    $partidos[] = [
                        'local' => $equipoLocal,
                        'visitante' => $equipoVisitante,
                    ];
                } else {
                    $partidos[] = [
                        'local' => $equipoVisitante,
                        'visitante' => $equipoLocal,
                    ];
                }
            }

            $jornadas[] = [
                'numero' => $j + 1,
                'partidos' => $partidos,
            ];

            $rotating[] = array_shift($rotating);
        }

        if ($idaYVuelta) {
            $jornadasVuelta = [];

            foreach ($jornadas as $idx => $jornada) {
                $partidosVuelta = [];

                foreach ($jornada['partidos'] as $partido) {
                    $partidosVuelta[] = [
                        'local' => $partido['visitante'],
                        'visitante' => $partido['local'],
                    ];
                }

                $jornadasVuelta[] = [
                    'numero' => $numJornadas + $idx + 1,
                    'partidos' => $partidosVuelta,
                ];
            }

            $jornadas = array_merge($jornadas, $jornadasVuelta);
        }

        return $jornadas;
    }

    public function generarPorGrupos(array $grupos, bool $idaYVuelta): array
    {
        $todasJornadas = [];
        $numeroGlobal = 1;

        foreach ($grupos as $grupoId => $equipos) {
            $jornadasGrupo = $this->generar($equipos, $idaYVuelta);

            foreach ($jornadasGrupo as $jornada) {
                $existe = false;

                foreach ($todasJornadas as &$jornadaExistente) {
                    if ($jornadaExistente['numero'] === $numeroGlobal) {
                        $jornadaExistente['partidos'] = array_merge(
                            $jornadaExistente['partidos'],
                            $jornada['partidos']
                        );
                        $existe = true;
                        break;
                    }
                }
                unset($jornadaExistente);

                if (! $existe) {
                    $todasJornadas[] = [
                        'numero' => $numeroGlobal,
                        'partidos' => $jornada['partidos'],
                        'grupo_id' => $grupoId,
                    ];
                }

                $numeroGlobal++;
            }
        }

        return $todasJornadas;
    }
}
