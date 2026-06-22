<?php

namespace App\Http\Controllers;

use App\Mail\JugadorRegistrado;
use App\Models\Player;
use App\Models\Team;
use App\Services\PlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class PlayerController extends Controller
{
    private function getMisEquiposIds(): array
    {
        $user = auth()->user();
        if ($user->hasRole('delegate')) {
            return Team::where('delegado_id', $user->id)->pluck('id')->toArray();
        }

        return [];
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $misEquiposIds = $this->getMisEquiposIds();
        $esDelegate = $user->hasRole('delegate');

        $players = Player::with('equipo')
            ->where('tenant_id', $user->tenant_id)
            ->when($esDelegate && ! $request->equipo_id, function ($q) use ($misEquiposIds) {
                $q->whereIn('equipo_id', $misEquiposIds);
            })
            ->when($request->search, function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->search}%");
            })
            ->when($request->equipo_id, function ($q) use ($request) {
                $q->where('equipo_id', $request->equipo_id);
            })
            ->when($request->posicion, function ($q) use ($request) {
                $q->where('posicion', $request->posicion);
            })
            ->when($request->estado, function ($q) use ($request) {
                $q->where('estado', $request->estado);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        // Obtener equipos para el filtro
        $equipos = Team::where('tenant_id', $user->tenant_id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('Players/Index', [
            'players' => $players,
            'equipos' => $equipos,
            'misEquiposIds' => $misEquiposIds,
            'esDelegate' => $esDelegate,
            'filters' => $request->only(['search', 'equipo_id', 'posicion', 'estado']),
            'flash' => [
                'success' => session('success'),
                'error' => session('error'),
            ],
            'constantes' => [
                'posiciones_jugador' => config('constants.posiciones_jugador', []),
                'estados_jugador' => config('constants.estados_jugador', []),
            ],
        ]);
    }

    public function create()
    {
        $tenant = auth()->user()->tenant;
        $user = auth()->user();
        $planService = new PlanService($tenant);

        // Obtener información del plan
        $playersCount = Player::where('tenant_id', $tenant->id)->count();
        $canCreate = $planService->canCreate('max_players', $playersCount);
        $remaining = $planService->remaining('max_players', $playersCount);
        $limit = $planService->getLimit('max_players');

        // Si es delegate, solo ve sus equipos para asignar jugadores
        if ($user->hasRole('delegate')) {
            $equipos = Team::where('tenant_id', $tenant->id)
                ->where('delegado_id', $user->id)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        } else {
            $equipos = Team::where('tenant_id', $tenant->id)
                ->select('id', 'name')
                ->orderBy('name')
                ->get();
        }

        return Inertia::render('Players/Create', [
            'equipos' => $equipos,
            'planInfo' => [
                'canCreate' => $canCreate,
                'remaining' => $remaining,
                'limit' => $limit,
                'currentCount' => $playersCount,
                'isUnlimited' => $planService->isUnlimited('max_players'),
            ],
            'constantes' => [
                'posiciones_jugador' => config('constants.posiciones_jugador', []),
                'estados_jugador' => config('constants.estados_jugador', []),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $user = auth()->user();
        $planService = new PlanService($tenant);

        // Validar límite del plan
        $playersCount = Player::where('tenant_id', $tenant->id)->count();

        if (! $planService->canCreate('max_players', $playersCount)) {
            $limit = $planService->getLimit('max_players');
            $message = $limit === -1
                ? 'No puedes crear más jugadores'
                : "Has alcanzado el límite de {$limit} jugadores de tu plan";

            return back()->with('error', $message);
        }

        // Delegate solo puede crear jugadores en su equipo
        if ($user->hasRole('delegate')) {
            $misEquipos = Team::where('delegado_id', $user->id)->pluck('id')->toArray();
            if (! in_array($request->equipo_id, $misEquipos)) {
                return back()->with('error', 'No puedes crear jugadores en un equipo que no te pertenece.');
            }
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'equipo_id' => 'required|exists:teams,id',
            'numero' => 'nullable|integer|min:1|max:99',
            'posicion' => 'nullable|string|in:portero,defensa,mediocampista,delantero,extremo,lateral,contencion,enganche',
            'edad' => 'nullable|integer|min:15|max:60',
            'curp' => 'nullable|string|size:18|unique:players,curp',
            'foto' => 'nullable|image|max:2048',
            'estado' => 'required|in:activo,suspendido,lesionado',
        ]);

        $fotoPath = null;

        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('players', 'public');
        }

        $player = Player::create([
            'tenant_id' => $tenant->id,
            'equipo_id' => $request->equipo_id,
            'nombre' => $request->nombre,
            'numero' => $request->numero,
            'posicion' => $request->posicion,
            'edad' => $request->edad,
            'curp' => $request->curp,
            'foto' => $fotoPath,
            'estado' => $request->estado,
        ]);

        $player->load('equipo.delegado');
        $this->enviarCorreoJugador($player);

        return redirect()->route('players.index')->with('success', 'Jugador creado correctamente');
    }

    public function edit(Player $player)
    {
        $this->authorizeTenant($player);

        // Delegate solo puede editar jugadores de su equipo
        if (auth()->user()->hasRole('delegate')) {
            $misEquipos = Team::where('delegado_id', auth()->id())->pluck('id')->toArray();
            if (! in_array($player->equipo_id, $misEquipos)) {
                abort(403, 'No puedes editar jugadores de otro equipo.');
            }
        }

        $tenant = auth()->user()->tenant;

        $equipos = Team::where('tenant_id', $tenant->id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('Players/Edit', [
            'player' => $player,
            'equipos' => $equipos,
            'constantes' => [
                'posiciones_jugador' => config('constants.posiciones_jugador', []),
                'estados_jugador' => config('constants.estados_jugador', []),
            ],
        ]);
    }

    public function update(Request $request, Player $player)
    {
        $this->authorizeTenant($player);

        // Delegate solo puede actualizar jugadores de su equipo
        if (auth()->user()->hasRole('delegate')) {
            $misEquipos = Team::where('delegado_id', auth()->id())->pluck('id')->toArray();
            if (! in_array($player->equipo_id, $misEquipos)) {
                abort(403, 'No puedes editar jugadores de otro equipo.');
            }
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'equipo_id' => 'required|exists:teams,id',
            'numero' => 'nullable|integer|min:1|max:99',
            'posicion' => 'nullable|string|in:portero,defensa,mediocampista,delantero,extremo,lateral,contencion,enganche',
            'edad' => 'nullable|integer|min:15|max:60',
            'curp' => 'nullable|string|size:18|unique:players,curp,'.$player->id,
            'foto' => 'nullable|image|max:2048',
            'estado' => 'required|in:activo,suspendido,lesionado',
        ]);

        $data = $request->except('foto', '_method');

        // Manejar la foto
        if ($request->hasFile('foto')) {
            // Eliminar foto anterior si existe
            if ($player->foto && \Storage::disk('public')->exists($player->foto)) {
                \Storage::disk('public')->delete($player->foto);
            }

            $data['foto'] = $request->file('foto')->store('players', 'public');
        }

        $player->update($data);

        return redirect()->route('players.index')->with('success', 'Jugador actualizado correctamente');
    }

    public function destroy(Player $player)
    {
        $this->authorizeTenant($player);

        // Delegate no puede eliminar jugadores
        if (auth()->user()->hasRole('delegate')) {
            abort(403, 'No tienes permiso para eliminar jugadores.');
        }

        // Eliminar foto si existe
        if ($player->foto && \Storage::disk('public')->exists($player->foto)) {
            \Storage::disk('public')->delete($player->foto);
        }

        $player->delete();

        return redirect()->route('players.index')->with('success', 'Jugador eliminado correctamente');
    }

    private function authorizeTenant(Player $player)
    {
        if ($player->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }
    }

    private function enviarCorreoJugador(Player $player): void
    {
        $email = $player->equipo->email ?? $player->equipo->delegado?->email;

        if (! $email) {
            return;
        }

        Mail::to($email)->queue(new JugadorRegistrado($player));
    }
}
