<?php

namespace App\Services;

class BracketGenerator
{
    public function generar(array $equiposClasificados, bool $idaYVuelta): array
    {
        $n = count($equiposClasificados);

        if ($n < 2 || ! $this->esPotenciaDe2($n)) {
            return [];
        }

        $fases = $this->calcularFases($n);
        $bracket = [];

        $primeraFase = $fases[0];
        $numLlaves = $n / 2;
        $partidos = [];

        for ($i = 0; $i < $numLlaves; $i++) {
            $posLocal = $i;
            $posVisitante = $n - 1 - $i;

            $llave = $this->generarNombreLlave($primeraFase, $i + 1);

            $partidos[] = [
                'local' => $equiposClasificados[$posLocal] ?? null,
                'visitante' => $equiposClasificados[$posVisitante] ?? null,
                'fase' => $primeraFase,
                'llave' => $llave,
                'orden' => 1,
                'es_vuelta' => false,
            ];

            if ($idaYVuelta) {
                $partidos[] = [
                    'local' => $equiposClasificados[$posVisitante] ?? null,
                    'visitante' => $equiposClasificados[$posLocal] ?? null,
                    'fase' => $primeraFase,
                    'llave' => $llave,
                    'orden' => 2,
                    'es_vuelta' => true,
                ];
            }
        }

        $bracket[] = [
            'fase' => $primeraFase,
            'partidos' => $partidos,
        ];

        for ($f = 1; $f < count($fases); $f++) {
            $fase = $fases[$f];
            $numLlavesFase = $numLlaves / pow(2, $f);
            $partidosFase = [];

            for ($i = 0; $i < $numLlavesFase; $i++) {
                $llave = $this->generarNombreLlave($fase, $i + 1);

                $partidosFase[] = [
                    'local' => null,
                    'visitante' => null,
                    'fase' => $fase,
                    'llave' => $llave,
                    'orden' => 1,
                    'es_vuelta' => false,
                    'placeholder' => 'Ganador vs Ganador',
                ];

                if ($idaYVuelta && $fase !== 'final') {
                    $partidosFase[] = [
                        'local' => null,
                        'visitante' => null,
                        'fase' => $fase,
                        'llave' => $llave,
                        'orden' => 2,
                        'es_vuelta' => true,
                        'placeholder' => 'Ganador vs Ganador',
                    ];
                }
            }

            $bracket[] = [
                'fase' => $fase,
                'partidos' => $partidosFase,
            ];
        }

        return $bracket;
    }

    private function calcularFases(int $n): array
    {
        $fases = [];

        if ($n >= 16) {
            $fases[] = 'octavos';
        }
        if ($n >= 8) {
            $fases[] = 'cuartos';
        }
        if ($n >= 4) {
            $fases[] = 'semifinal';
        }
        $fases[] = 'final';

        return $fases;
    }

    private function generarNombreLlave(string $fase, int $numero): string
    {
        $prefijos = [
            'octavos' => 'OF',
            'cuartos' => 'QF',
            'semifinal' => 'SF',
            'final' => 'F',
            'tercer_lugar' => 'TL',
        ];

        return ($prefijos[$fase] ?? strtoupper(substr($fase, 0, 2))).$numero;
    }

    private function esPotenciaDe2(int $n): bool
    {
        return $n > 0 && ($n & ($n - 1)) === 0;
    }
}
