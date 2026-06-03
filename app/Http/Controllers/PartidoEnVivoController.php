<?php

namespace App\Http\Controllers;

use App\Enums\PermissionEnum;
use App\Http\Requests\CambiarEstadoPartidoRequest;
use App\Http\Requests\StorePartidoAsistenciaRequest;
use App\Http\Requests\StorePartidoEventoRequest;
use App\Models\Partido;
use App\Models\PartidoEvento;
use App\Services\PartidoEnVivoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PartidoEnVivoController extends Controller
{
    public function __construct(
        private readonly PartidoEnVivoService $service
    ) {}

    public function show(Partido $partido)
    {
        $this->authorize(PermissionEnum::MATCHES_RECORD_EVENTS);

        $datos = $this->service->obtenerDatosEnVivo($partido);

        $user = auth()->user();
        $esArbitro = $user->hasRole('referee');
        $esAdmin = $user->hasRole('admin') || $user->hasRole('manager');
        $esDelegadoLocal = false;
        $esDelegadoVisitante = false;

        if ($user->hasRole('delegate')) {
            $equipoLocal = $partido->equipoLocal?->equipo;
            $equipoVisitante = $partido->equipoVisitante?->equipo;
            $esDelegadoLocal = $equipoLocal && $equipoLocal->delegado_id === $user->id;
            $esDelegadoVisitante = $equipoVisitante && $equipoVisitante->delegado_id === $user->id;
        }

        $delegadoPuedeAsistir = $partido->torneo?->configuracion_asistencia_delegado ?? false;

        return Inertia::render('Partidos/EnVivo/Show', [
            'partido' => $datos['partido'],
            'eventos' => $datos['eventos'],
            'asistencias' => $datos['asistencias'],
            'faltas_local' => $datos['faltas_local'],
            'faltas_visitante' => $datos['faltas_visitante'],
            'alerta_penal_local' => $datos['alerta_penal_local'],
            'alerta_penal_visitante' => $datos['alerta_penal_visitante'],
            'expulsados_local' => $datos['expulsados_local'],
            'expulsados_visitante' => $datos['expulsados_visitante'],
            'puede_registrar_eventos' => $esArbitro || $esAdmin,
            'puede_cambiar_estado' => $esArbitro || $esAdmin,
            'puede_registrar_asistencia_local' => $esArbitro || $esAdmin || ($esDelegadoLocal && $delegadoPuedeAsistir),
            'puede_registrar_asistencia_visitante' => $esArbitro || $esAdmin || ($esDelegadoVisitante && $delegadoPuedeAsistir),
            'tipos_evento' => \App\Enums\PartidoEventoTipoEnum::all(),
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
        ]);
    }

    public function iniciar(Partido $partido): RedirectResponse
    {
        $this->authorize(PermissionEnum::MATCHES_RECORD_EVENTS);
        $this->service->cambiarEstado($partido, 'en_juego');

        return redirect()->route('partidos.en-vivo.show', $partido->id)
            ->with('success', 'Partido iniciado. Primera mitad en juego.');
    }

    public function descanso(Partido $partido): RedirectResponse
    {
        $this->authorize(PermissionEnum::MATCHES_RECORD_EVENTS);
        $this->service->cambiarEstado($partido, 'descanso');

        return redirect()->route('partidos.en-vivo.show', $partido->id)
            ->with('success', 'Medio tiempo. Pase de lista disponible.');
    }

    public function segundaMitad(Partido $partido): RedirectResponse
    {
        $this->authorize(PermissionEnum::MATCHES_RECORD_EVENTS);
        $this->service->cambiarEstado($partido, 'en_juego');

        return redirect()->route('partidos.en-vivo.show', $partido->id)
            ->with('success', 'Segunda mitad iniciada.');
    }

    public function finalizar(Partido $partido): RedirectResponse
    {
        $this->authorize(PermissionEnum::MATCHES_RECORD_EVENTS);
        $this->service->cambiarEstado($partido, 'finalizado');

        return redirect()->route('partidos.en-vivo.show', $partido->id)
            ->with('success', 'Partido finalizado. Marcador guardado.');
    }

    public function storeEvento(StorePartidoEventoRequest $request, Partido $partido): RedirectResponse
    {
        $this->authorize(PermissionEnum::MATCHES_RECORD_EVENTS);

        if (! $partido->puedeRegistrarEventos()) {
            return back()->with('error', 'No se pueden registrar eventos en el estado actual del partido.');
        }

        $this->service->registrarEvento($partido, $request->validated());

        return redirect()->route('partidos.en-vivo.show', $partido->id)
            ->with('success', 'Evento registrado correctamente.');
    }

    public function destroyEvento(PartidoEvento $evento): RedirectResponse
    {
        $this->authorize(PermissionEnum::MATCHES_RECORD_EVENTS);

        $partidoId = $evento->partido_id;
        $this->service->eliminarEvento($evento);

        return redirect()->route('partidos.en-vivo.show', $partidoId)
            ->with('success', 'Evento eliminado.');
    }

    public function storeAsistencias(StorePartidoAsistenciaRequest $request, Partido $partido): RedirectResponse
    {
        $user = auth()->user();
        $esArbitro = $user->hasRole('referee');
        $esAdmin = $user->hasRole('admin') || $user->hasRole('manager');
        $delegadoPuede = $partido->torneo?->configuracion_asistencia_delegado ?? false;
        $esDelegadoLocal = false;
        $esDelegadoVisitante = false;

        if ($user->hasRole('delegate')) {
            $esDelegadoLocal = $partido->equipoLocal?->equipo?->delegado_id === $user->id;
            $esDelegadoVisitante = $partido->equipoVisitante?->equipo?->delegado_id === $user->id;
        }

        if (! $esArbitro && ! $esAdmin && ! ($delegadoPuede && ($esDelegadoLocal || $esDelegadoVisitante))) {
            return back()->with('error', 'No tienes permiso para registrar asistencias.');
        }

        $this->service->registrarAsistencias($partido, $request->validated('asistencias'));

        return redirect()->route('partidos.en-vivo.show', $partido->id)
            ->with('success', 'Pase de lista guardado correctamente.');
    }
}
