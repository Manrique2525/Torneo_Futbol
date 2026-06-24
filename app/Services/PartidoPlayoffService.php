<?php

namespace App\Services;

use App\Models\Partido;
use App\Models\TorneoEquipo;
use Illuminate\Database\Eloquent\Collection;

class PartidoPlayoffService
{
    private array $nextFase = [
        'octavos' => 'cuartos',
        'cuartos' => 'semifinal',
        'semifinal' => 'final',
    ];

    private array $prefijos = [
        'octavos' => 'OF',
        'cuartos' => 'QF',
        'semifinal' => 'SF',
        'final' => 'F',
    ];

    public function avanzarGanador(Partido $partido): void
    {
        if ($partido->fase === 'regular' || $partido->fase === 'final') {
            return;
        }

        $partidosLlave = Partido::where('torneo_id', $partido->torneo_id)
            ->where('fase', $partido->fase)
            ->where('llave_bracket', $partido->llave_bracket)
            ->with(['equipoLocal', 'equipoVisitante'])
            ->get();

        if ($partidosLlave->contains(fn ($p) => $p->estado !== 'finalizado')) {
            return;
        }

        $ganador = $this->determinarGanador($partidosLlave);

        if (! $ganador) {
            return;
        }

        $siguiente = $this->buscarSiguientePartido($partido);

        if (! $siguiente) {
            return;
        }

        $columna = $this->esLocal($partido) ? 'equipo_local_id' : 'equipo_visitante_id';

        if ($siguiente->{$columna} !== null) {
            return;
        }

        $siguiente->update([$columna => $ganador->id]);
    }

    public function determinarGanador(Collection $partidosLlave): ?TorneoEquipo
    {
        if ($partidosLlave->count() === 1) {
            return $this->ganadorPartidoUnico($partidosLlave->first());
        }

        $ida = $partidosLlave->firstWhere('es_vuelta', false);
        $vuelta = $partidosLlave->firstWhere('es_vuelta', true);

        if (! $ida || ! $vuelta) {
            return null;
        }

        return $this->ganadorIdaVuelta($ida, $vuelta);
    }

    public function buscarSiguientePartido(Partido $partido): ?Partido
    {
        $nextFase = $this->nextFase[$partido->fase] ?? null;

        if (! $nextFase) {
            return null;
        }

        $llaveNum = (int) preg_replace('/[^0-9]/', '', $partido->llave_bracket);
        $nextLlaveNum = (int) ceil($llaveNum / 2);
        $nextLlave = ($this->prefijos[$nextFase] ?? strtoupper(substr($nextFase, 0, 2))).$nextLlaveNum;

        return Partido::where('torneo_id', $partido->torneo_id)
            ->where('fase', $nextFase)
            ->where('llave_bracket', $nextLlave)
            ->first();
    }

    public function esLocal(Partido $partido): bool
    {
        $llaveNum = (int) preg_replace('/[^0-9]/', '', $partido->llave_bracket);

        return $llaveNum % 2 === 1;
    }

    private function ganadorPartidoUnico(Partido $p): ?TorneoEquipo
    {
        if ($p->goles_local > $p->goles_visitante) {
            return $p->equipoLocal;
        }

        if ($p->goles_visitante > $p->goles_local) {
            return $p->equipoVisitante;
        }

        if ($p->goles_penales_local !== null && $p->goles_penales_visitante !== null) {
            return $p->goles_penales_local > $p->goles_penales_visitante
                ? $p->equipoLocal
                : $p->equipoVisitante;
        }

        return null;
    }

    private function ganadorIdaVuelta(Partido $ida, Partido $vuelta): ?TorneoEquipo
    {
        $equipoA = $ida->equipoLocal;
        $equipoB = $ida->equipoVisitante;

        $golesA = $ida->goles_local + $vuelta->goles_visitante;
        $golesB = $ida->goles_visitante + $vuelta->goles_local;

        if ($golesA > $golesB) {
            return $equipoA;
        }

        if ($golesB > $golesA) {
            return $equipoB;
        }

        $golesVisitanteA = $vuelta->goles_visitante;
        $golesVisitanteB = $ida->goles_visitante;

        if ($golesVisitanteA > $golesVisitanteB) {
            return $equipoA;
        }

        if ($golesVisitanteB > $golesVisitanteA) {
            return $equipoB;
        }

        $pA = $vuelta->goles_penales_local ?? $ida->goles_penales_local;
        $pB = $vuelta->goles_penales_visitante ?? $ida->goles_penales_visitante;

        if ($pA !== null && $pB !== null && $pA !== $pB) {
            return $pA > $pB ? $equipoA : $equipoB;
        }

        return null;
    }
}
