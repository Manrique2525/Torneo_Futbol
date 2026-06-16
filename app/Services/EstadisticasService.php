<?php

namespace App\Services;

use App\Enums\PartidoEventoTipoEnum;
use App\Models\PartidoEvento;
use App\Models\Torneo;
use Illuminate\Support\Facades\DB;

class EstadisticasService
{
    private const GOL_TIPOS = [
        PartidoEventoTipoEnum::GOL,
        PartidoEventoTipoEnum::GOL_PENAL,
    ];

    public function globales(Torneo $torneo): array
    {
        $partidoIds = $torneo->partidos()
            ->where('estado', 'finalizado')
            ->pluck('id');

        if ($partidoIds->isEmpty()) {
            return $this->vacia();
        }

        return [
            'goleo' => $this->goleo($partidoIds),
            'asistencias' => $this->asistencias($partidoIds),
            'tarjetas_amarillas' => $this->contarPorTipo($partidoIds, PartidoEventoTipoEnum::TARJETA_AMARILLA),
            'tarjetas_rojas' => $this->contarPorTipo($partidoIds, PartidoEventoTipoEnum::TARJETA_ROJA),
            'faltas' => $this->contarPorTipo($partidoIds, PartidoEventoTipoEnum::FALTA),
        ];
    }

    public function porEquipo(Torneo $torneo, int $torneoEquipoId): array
    {
        $partidoIds = $torneo->partidos()
            ->where('estado', 'finalizado')
            ->where(function ($q) use ($torneoEquipoId) {
                $q->where('equipo_local_id', $torneoEquipoId)
                    ->orWhere('equipo_visitante_id', $torneoEquipoId);
            })
            ->pluck('id');

        if ($partidoIds->isEmpty()) {
            return $this->vacia();
        }

        $teamId = $torneo->equiposAprobados()
            ->wherePivot('id', $torneoEquipoId)
            ->first()?->id;

        if (! $teamId) {
            return $this->vacia();
        }

        return [
            'goleo' => $this->goleo($partidoIds, $teamId),
            'asistencias' => $this->asistencias($partidoIds, $teamId),
            'tarjetas_amarillas' => $this->contarPorTipo($partidoIds, PartidoEventoTipoEnum::TARJETA_AMARILLA, $teamId),
            'tarjetas_rojas' => $this->contarPorTipo($partidoIds, PartidoEventoTipoEnum::TARJETA_ROJA, $teamId),
            'faltas' => $this->contarPorTipo($partidoIds, PartidoEventoTipoEnum::FALTA, $teamId),
        ];
    }

    private function goleo($partidoIds, ?int $teamId = null): array
    {
        $query = PartidoEvento::whereIn('partido_id', $partidoIds)
            ->whereIn('tipo', array_map(fn ($e) => $e->value, self::GOL_TIPOS))
            ->select('jugador_id', 'equipo_id', DB::raw('COUNT(*) as total'))
            ->groupBy('jugador_id', 'equipo_id');

        if ($teamId) {
            $query->where('equipo_id', $teamId);
        }

        return $query->get()
            ->map(fn ($e) => $this->mapearFila($e, 'goles'))
            ->sortByDesc('total')
            ->values()
            ->toArray();
    }

    private function asistencias($partidoIds, ?int $teamId = null): array
    {
        $query = PartidoEvento::whereIn('partido_id', $partidoIds)
            ->whereIn('tipo', array_map(fn ($e) => $e->value, self::GOL_TIPOS))
            ->whereNotNull('jugador_relacionado_id')
            ->select('jugador_relacionado_id', 'equipo_id', DB::raw('COUNT(*) as total'))
            ->groupBy('jugador_relacionado_id', 'equipo_id');

        if ($teamId) {
            $query->where('equipo_id', $teamId);
        }

        return $query->get()
            ->map(fn ($e) => $this->mapearFila($e, 'asistencias', 'jugador_relacionado_id'))
            ->sortByDesc('total')
            ->values()
            ->toArray();
    }

    private function contarPorTipo($partidoIds, PartidoEventoTipoEnum $tipo, ?int $teamId = null): array
    {
        $query = PartidoEvento::whereIn('partido_id', $partidoIds)
            ->where('tipo', $tipo->value)
            ->select('jugador_id', 'equipo_id', DB::raw('COUNT(*) as total'))
            ->groupBy('jugador_id', 'equipo_id');

        if ($teamId) {
            $query->where('equipo_id', $teamId);
        }

        return $query->get()
            ->map(fn ($e) => $this->mapearFila($e))
            ->sortByDesc('total')
            ->values()
            ->toArray();
    }

    private function mapearFila($e, string $campo = 'total', string $jugadorCol = 'jugador_id'): array
    {
        $jugador = $e->$jugadorCol ? $e->jugador : null;
        if ($jugadorCol === 'jugador_relacionado_id') {
            $jugador = $e->jugadorRelacionado;
        }

        return [
            'jugador_nombre' => $jugador?->nombre ?? '—',
            'jugador_numero' => $jugador?->numero,
            'equipo_nombre' => $e->equipo?->name ?? '—',
            'equipo_shield' => $e->equipo?->shield,
            $campo => (int) $e->total,
        ];
    }

    private function vacia(): array
    {
        return [
            'goleo' => [],
            'asistencias' => [],
            'tarjetas_amarillas' => [],
            'tarjetas_rojas' => [],
            'faltas' => [],
        ];
    }
}
