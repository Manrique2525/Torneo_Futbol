<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\PlanService;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $teams = Team::with('delegado')
            ->where('tenant_id', auth()->user()->tenant_id)
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Teams/Index', [
            'teams' => $teams,
            'filters' => $request->only('search'),
            'flash' => [
                'success' => session('success'),
                'error' => session('error')
            ],
            'constantes' => [
                'colores_equipos' => config('constants.colores_equipos', []),
            ],
        ]);
    }

    public function create()
    {
        $tenant = auth()->user()->tenant;
        $planService = new PlanService($tenant);
        
        // Obtener información del plan
        $teamsCount = Team::where('tenant_id', $tenant->id)->count();
        $canCreate = $planService->canCreateTeam($teamsCount);
        $remaining = $planService->remaining('max_teams', $teamsCount);
        $limit = $planService->getLimit('max_teams');
        
        $delegados = User::where('tenant_id', $tenant->id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('Teams/Create', [
            'delegados' => $delegados,
            'planInfo' => [
                'canCreate' => $canCreate,
                'remaining' => $remaining,
                'limit' => $limit,
                'currentCount' => $teamsCount,
                'isUnlimited' => $planService->isUnlimited('max_teams'),
            ],
            'constantes' => [
                'colores_equipos' => config('constants.colores_equipos', []),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $planService = new PlanService($tenant);

        // Validar límite del plan
        $teamsCount = Team::where('tenant_id', $tenant->id)->count();
        
        if (!$planService->canCreateTeam($teamsCount)) {
            $limit = $planService->getLimit('max_teams');
            $message = $limit === -1 
                ? 'No puedes crear más equipos' 
                : "Has alcanzado el límite de {$limit} equipos de tu plan";
            
            return back()->with('error', $message);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'colors' => 'nullable|string',
            'delegado_id' => 'nullable|exists:users,id',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'shield' => 'nullable|image|max:2048',
            'status' => 'nullable|in:active,suspended,inactive',
        ]);

        $shieldPath = null;

        if ($request->hasFile('shield')) {
            $shieldPath = $request->file('shield')->store('teams', 'public');
        }

        Team::create([
            'tenant_id' => $tenant->id,
            'name' => $request->name,
            'colors' => $request->colors,
            'delegado_id' => $request->delegado_id,
            'phone' => $request->phone,
            'email' => $request->email,
            'shield' => $shieldPath,
            'status' => $request->status ?? 'active',
        ]);

        return redirect()->route('teams.index')->with('success', 'Equipo creado correctamente');
    }

    public function edit(Team $team)
    {
        $this->authorizeTenant($team);
        
        $tenant = auth()->user()->tenant;

        $delegados = User::where('tenant_id', $tenant->id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        return Inertia::render('Teams/Edit', [
            'team' => $team,
            'delegados' => $delegados,
            'constantes' => [
                'colores_equipos' => config('constants.colores_equipos', []),
            ],
        ]);
    }

    public function update(Request $request, Team $team)
    {
        $this->authorizeTenant($team);

        $request->validate([
            'name' => 'required|string|max:255',
            'colors' => 'nullable|string',
            'delegado_id' => 'nullable|exists:users,id',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'status' => 'required|in:active,suspended,inactive',
            'shield' => 'nullable|image|max:2048',
        ]);

        $data = $request->except('shield', '_method');
        
        // Manejar el escudo
        if ($request->hasFile('shield')) {
            // Eliminar escudo anterior si existe
            if ($team->shield && \Storage::disk('public')->exists($team->shield)) {
                \Storage::disk('public')->delete($team->shield);
            }
            
            $data['shield'] = $request->file('shield')->store('teams', 'public');
        }

        $team->update($data);

        return redirect()->route('teams.index')->with('success', 'Equipo actualizado correctamente');
    }

    public function destroy(Team $team)
    {
        $this->authorizeTenant($team);

        // Eliminar escudo si existe
        if ($team->shield && \Storage::disk('public')->exists($team->shield)) {
            \Storage::disk('public')->delete($team->shield);
        }

        $team->delete();

        return redirect()->route('teams.index')->with('success', 'Equipo eliminado correctamente');
    }

    private function authorizeTenant(Team $team)
    {
        if ($team->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }
    }
}