<?php

namespace App\Http\Controllers;

use App\Enums\PermissionEnum;
use App\Models\Torneo;
use App\Models\TorneoGrupo;
use App\Services\StandingsService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

class StandingsController extends Controller
{
    public function __construct(
        private readonly StandingsService $standingsService
    ) {}

    public function index(Torneo $torneo)
    {
        $this->authorize(PermissionEnum::STANDINGS_VIEW);

        $grupos = TorneoGrupo::where('torneo_id', $torneo->id)
            ->orderBy('orden')
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        $standings = $torneo->standings()
            ->with(['torneoEquipo.equipo:id,name,shield'])
            ->get()
            ->map(fn ($s) => [
                'id' => $s->id,
                'torneo_equipo_id' => $s->torneo_equipo_id,
                'posicion_posiciones' => $s->posicion_posiciones,
                'posicion_rendimiento' => $s->posicion_rendimiento,
                'equipo' => [
                    'id' => $s->torneoEquipo?->equipo?->id,
                    'nombre' => $s->torneoEquipo?->equipo?->name ?? 'Equipo #'.$s->torneo_equipo_id,
                    'shield' => $s->torneoEquipo?->equipo?->shield,
                ],
                'pj' => $s->pj,
                'pg' => $s->pg,
                'pe' => $s->pe,
                'pp' => $s->pp,
                'gf' => $s->gf,
                'gc' => $s->gc,
                'dg' => $s->dg,
                'pts' => $s->pts,
                'fair_play' => $s->fair_play,
                'torneo_grupo_id' => $s->torneo_grupo_id,
            ]);

        $gruposConStandings = $grupos->map(fn ($g) => [
            'id' => $g->id,
            'nombre' => $g->nombre,
            'standings' => $standings->where('torneo_grupo_id', $g->id)->values(),
        ]);

        // Si no hay grupos distintos o solo hay un "General"
        if ($gruposConStandings->isEmpty()) {
            $gruposConStandings = collect([[
                'id' => null,
                'nombre' => 'General',
                'standings' => $standings->values(),
            ]]);
        }

        return Inertia::render('Torneos/Posiciones', [
            'torneo' => [
                'id' => $torneo->id,
                'nombre' => $torneo->nombre,
                'tipo' => $torneo->tipo,
                'estado' => $torneo->estado,
            ],
            'es_liga' => $torneo->tipo === 'liga',
            'grupos' => $gruposConStandings,
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    public function recalcular(Torneo $torneo): RedirectResponse
    {
        $this->authorize(PermissionEnum::STANDINGS_RECALCULATE);

        $this->standingsService->recalcular($torneo);

        return redirect()->route('standings.index', $torneo->id)
            ->with('success', 'Tabla de posiciones recalculada correctamente.');
    }
}
