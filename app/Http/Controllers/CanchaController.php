<?php

namespace App\Http\Controllers;

use App\Enums\PermissionEnum;
use App\Models\Cancha;
use App\Models\DisponibilidadCancha;
use App\Services\PlanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CanchaController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize(PermissionEnum::FIELDS_VIEW);

        $constants = config('constants');

        return Inertia::render('Canchas/Index', [
            'canchas' => Cancha::query()
                ->when($request->search, function ($q) use ($request) {
                    $q->where('nombre', 'like', "%{$request->search}%");
                })
                ->when($request->tipo && $request->tipo !== 'todos', function ($q) use ($request) {
                    $q->where('tipo', $request->tipo);
                })
                ->when($request->estado && $request->estado !== 'todos', function ($q) use ($request) {
                    $q->where('estado', $request->estado);
                })
                ->latest()
                ->paginate(10)
                ->withQueryString(),

            'filters' => $request->only(['search', 'tipo', 'estado']),
            'flash' => [
                'success' => session('success'),
                'error'   => session('error'),
            ],
            'constantes' => $constants ?? [],
        ]);
    }

    public function create()
    {
        $this->authorize(PermissionEnum::FIELDS_CREATE);

        $tenant = auth()->user()->tenant;
        $planService = new PlanService($tenant);

        $canchasCount = Cancha::count();
        $canCreate = $planService->canCreate('max_fields', $canchasCount);
        $remaining = $planService->remaining('max_fields', $canchasCount);
        $limit = $planService->getLimit('max_fields');

        return Inertia::render('Canchas/Create', [
            'planInfo' => [
                'canCreate'     => $canCreate,
                'remaining'     => $remaining,
                'limit'         => $limit,
                'currentCount'  => $canchasCount,
                'isUnlimited'   => $planService->isUnlimited('max_fields'),
            ],
            'constantes' => [
                'tipos_cancha'   => config('constants.tipos_cancha', []),
                'estados_cancha' => config('constants.estados_cancha', []),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize(PermissionEnum::FIELDS_CREATE);

        $tenant = auth()->user()->tenant;
        $planService = new PlanService($tenant);

        $canchasCount = Cancha::count();

        if (! $planService->canCreate('max_fields', $canchasCount)) {
            $limit = $planService->getLimit('max_fields');
            $message = $limit === -1
                ? 'No puedes crear más canchas.'
                : "Has alcanzado el límite de {$limit} canchas de tu plan.";

            return back()->with('error', $message);
        }

        $constants = config('constants');

        $validated = $request->validate([
            'nombre'     => 'required|string|max:255',
            'direccion'  => 'nullable|string|max:1000',
            'tipo'       => 'required|in:'.implode(',', array_keys($constants['tipos_cancha'] ?? [])),
            'capacidad'  => 'nullable|integer|min:0|max:999999',
            'latitud'    => 'nullable|numeric|between:-90,90',
            'longitud'   => 'nullable|numeric|between:-180,180',
            'estado'     => 'required|in:'.implode(',', array_keys($constants['estados_cancha'] ?? [])),
        ], [], [
            'nombre'     => 'nombre',
            'direccion'  => 'dirección',
            'tipo'       => 'tipo',
            'capacidad'  => 'capacidad',
            'latitud'    => 'latitud',
            'longitud'   => 'longitud',
            'estado'     => 'estado',
        ]);

        $cancha = Cancha::create($validated);

        $this->syncDisponibilidades($cancha, $request->input('disponibilidades', []));

        return redirect()->route('canchas.index')
            ->with('success', 'Cancha creada correctamente.');
    }

    public function edit(Cancha $cancha)
    {
        $this->authorize(PermissionEnum::FIELDS_UPDATE);

        $disponibilidades = $cancha->disponibilidades()
            ->select('id', 'cancha_id', 'dia_semana', 'hora_inicio', 'hora_fin')
            ->get()
            ->map(fn ($d) => [
                'dia_semana'  => $d->dia_semana,
                'hora_inicio' => $d->hora_inicio instanceof \Illuminate\Support\Carbon
                    ? $d->hora_inicio->format('H:i')
                    : substr((string) $d->hora_inicio, 0, 5),
                'hora_fin'    => $d->hora_fin instanceof \Illuminate\Support\Carbon
                    ? $d->hora_fin->format('H:i')
                    : substr((string) $d->hora_fin, 0, 5),
            ]);

        return Inertia::render('Canchas/Edit', [
            'cancha'           => $cancha,
            'disponibilidades' => $disponibilidades,
            'constantes' => [
                'tipos_cancha'   => config('constants.tipos_cancha', []),
                'estados_cancha' => config('constants.estados_cancha', []),
            ],
        ]);
    }

    public function update(Request $request, Cancha $cancha): RedirectResponse
    {
        $this->authorize(PermissionEnum::FIELDS_UPDATE);

        $constants = config('constants');

        $validated = $request->validate([
            'nombre'     => 'required|string|max:255',
            'direccion'  => 'nullable|string|max:1000',
            'tipo'       => 'required|in:'.implode(',', array_keys($constants['tipos_cancha'] ?? [])),
            'capacidad'  => 'nullable|integer|min:0|max:999999',
            'latitud'    => 'nullable|numeric|between:-90,90',
            'longitud'   => 'nullable|numeric|between:-180,180',
            'estado'     => 'required|in:'.implode(',', array_keys($constants['estados_cancha'] ?? [])),
        ], [], [
            'nombre'     => 'nombre',
            'direccion'  => 'dirección',
            'tipo'       => 'tipo',
            'capacidad'  => 'capacidad',
            'latitud'    => 'latitud',
            'longitud'   => 'longitud',
            'estado'     => 'estado',
        ]);

        $cancha->update($validated);

        $this->syncDisponibilidades($cancha, $request->input('disponibilidades', []));

        return redirect()->route('canchas.index')
            ->with('success', 'Cancha actualizada correctamente.');
    }

    public function destroy(Cancha $cancha): RedirectResponse
    {
        $this->authorize(PermissionEnum::FIELDS_DELETE);

        $cancha->delete();

        return redirect()->route('canchas.index')
            ->with('success', 'Cancha eliminada correctamente.');
    }

    protected function syncDisponibilidades(Cancha $cancha, array $disponibilidades): void
    {
        $cancha->disponibilidades()->delete();

        foreach ($disponibilidades as $item) {
            if (empty($item['hora_inicio']) || empty($item['hora_fin'])) {
                continue;
            }

            $cancha->disponibilidades()->create([
                'dia_semana'  => (int) $item['dia_semana'],
                'hora_inicio' => $item['hora_inicio'],
                'hora_fin'    => $item['hora_fin'],
            ]);
        }
    }
}
