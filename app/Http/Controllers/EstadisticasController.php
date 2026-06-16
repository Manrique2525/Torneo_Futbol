<?php

namespace App\Http\Controllers;

use App\Enums\PermissionEnum;
use App\Models\Torneo;
use App\Services\EstadisticasService;
use Inertia\Inertia;

class EstadisticasController extends Controller
{
    public function __construct(
        private readonly EstadisticasService $service
    ) {}

    public function index(Torneo $torneo)
    {
        $this->authorize(PermissionEnum::STATS_VIEW);

        $estadisticas = $this->service->globales($torneo);

        return Inertia::render('Torneos/Estadisticas', [
            'torneo' => [
                'id' => $torneo->id,
                'nombre' => $torneo->nombre,
                'tipo' => $torneo->tipo,
            ],
            'estadisticas' => $estadisticas,
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    public function equipo(Torneo $torneo, int $torneoEquipoId)
    {
        $this->authorize(PermissionEnum::STATS_VIEW);

        return response()->json(
            $this->service->porEquipo($torneo, $torneoEquipoId)
        );
    }
}
