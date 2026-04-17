<?php

namespace App\Http\Controllers;

use App\Models\Arbitro;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Services\PlanService;

class ArbitroController extends Controller
{
    public function index(Request $request)
    {
        $arbitros = Arbitro::where('tenant_id', auth()->user()->tenant_id)
            ->when($request->search, function ($q) use ($request) {
                $q->where('nombre', 'like', "%{$request->search}%");
            })
            ->when($request->nivel, function ($q) use ($request) {
                $q->where('nivel', $request->nivel);
            })
            ->when($request->disponible !== null, function ($q) use ($request) {
                $q->where('disponible', $request->disponible);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Arbitros/Index', [
            'arbitros' => $arbitros,
            'filters' => $request->only(['search', 'nivel', 'disponible']),
            'flash' => [
                'success' => session('success'),
                'error' => session('error')
            ],
            'constantes' => [
                'niveles_arbitro' => config('constants.niveles_arbitro', []),
            ],
        ]);
    }

    public function create()
    {
        $tenant = auth()->user()->tenant;
        $planService = new PlanService($tenant);
        
        // Obtener información del plan
        $arbitrosCount = Arbitro::where('tenant_id', $tenant->id)->count();
        $canCreate = $planService->canCreate('max_referees', $arbitrosCount);
        $remaining = $planService->remaining('max_referees', $arbitrosCount);
        $limit = $planService->getLimit('max_referees');

        return Inertia::render('Arbitros/Create', [
            'planInfo' => [
                'canCreate' => $canCreate,
                'remaining' => $remaining,
                'limit' => $limit,
                'currentCount' => $arbitrosCount,
                'isUnlimited' => $planService->isUnlimited('max_referees'),
            ],
            'constantes' => [
                'niveles_arbitro' => config('constants.niveles_arbitro', []),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $tenant = auth()->user()->tenant;
        $planService = new PlanService($tenant);

        // Validar límite del plan
        $arbitrosCount = Arbitro::where('tenant_id', $tenant->id)->count();
        
        if (!$planService->canCreate('max_referees', $arbitrosCount)) {
            $limit = $planService->getLimit('max_referees');
            $message = $limit === -1 
                ? 'No puedes crear más árbitros' 
                : "Has alcanzado el límite de {$limit} árbitros de tu plan";
            
            return back()->with('error', $message);
        }

        $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'nivel' => 'required|in:nacional,regional,local,internacional',
            'disponible' => 'boolean',
            'pago_por_partido' => 'nullable|numeric|min:0|max:999999.99',
        ]);

        Arbitro::create([
            'tenant_id' => $tenant->id,
            'nombre' => $request->nombre,
            'telefono' => $request->telefono,
            'email' => $request->email,
            'nivel' => $request->nivel,
            'disponible' => $request->disponible ?? true,
            'pago_por_partido' => $request->pago_por_partido,
        ]);

        return redirect()->route('arbitros.index')->with('success', 'Árbitro creado correctamente');
    }

    public function edit(Arbitro $arbitro)
    {
        $this->authorizeTenant($arbitro);

        return Inertia::render('Arbitros/Edit', [
            'arbitro' => $arbitro,
            'constantes' => [
                'niveles_arbitro' => config('constants.niveles_arbitro', []),
            ],
        ]);
    }

    public function update(Request $request, Arbitro $arbitro)
    {
        $this->authorizeTenant($arbitro);

        $request->validate([
            'nombre' => 'required|string|max:255',
            'telefono' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'nivel' => 'required|in:nacional,regional,local,internacional',
            'disponible' => 'boolean',
            'pago_por_partido' => 'nullable|numeric|min:0|max:999999.99',
        ]);

        $arbitro->update($request->all());

        return redirect()->route('arbitros.index')->with('success', 'Árbitro actualizado correctamente');
    }

    public function destroy(Arbitro $arbitro)
    {
        $this->authorizeTenant($arbitro);
        $arbitro->delete();

        return redirect()->route('arbitros.index')->with('success', 'Árbitro eliminado correctamente');
    }

    private function authorizeTenant(Arbitro $arbitro)
    {
        if ($arbitro->tenant_id !== auth()->user()->tenant_id) {
            abort(403);
        }
    }
}