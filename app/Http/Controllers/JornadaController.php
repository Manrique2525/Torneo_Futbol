<?php

namespace App\Http\Controllers;

use App\Enums\PermissionEnum;
use App\Models\Jornada;
use App\Models\Torneo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class JornadaController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize(PermissionEnum::MATCH_DAYS_VIEW);

        $constants = config('constants');

        $torneos = Torneo::query()
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Jornadas/Index', [
            'jornadas' => Jornada::query()
                ->with('torneo')
                ->when($request->search, function ($q) use ($request) {
                    $q->where('nombre', 'like', "%{$request->search}%");
                })
                ->when($request->torneo_id && $request->torneo_id !== 'todos', function ($q) use ($request) {
                    $q->where('torneo_id', $request->torneo_id);
                })
                ->when($request->estado && $request->estado !== 'todos', function ($q) use ($request) {
                    $q->where('estado', $request->estado);
                })
                ->latest()
                ->paginate(10)
                ->withQueryString(),

            'filters'   => $request->only(['search', 'torneo_id', 'estado']),
            'flash'     => [
                'success' => session('success'),
                'error'   => session('error'),
            ],
            'constantes' => $constants ?? [],
            'torneos'    => $torneos,
        ]);
    }

    public function create()
    {
        $this->authorize(PermissionEnum::MATCH_DAYS_CREATE);

        $torneos = Torneo::query()
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Jornadas/Create', [
            'torneos'    => $torneos,
            'constantes' => [
                'estados_jornada' => config('constants.estados_jornada', []),
            ],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize(PermissionEnum::MATCH_DAYS_CREATE);

        $tenantId = auth()->user()->tenant_id;
        $constants = config('constants');

        $validated = $request->validate([
            'torneo_id'   => [
                'required',
                'integer',
                Rule::exists('torneos', 'id')->where('tenant_id', $tenantId),
            ],
            'nombre'      => 'required|string|max:255',
            'numero'      => [
                'required',
                'integer',
                'min:1',
                'max:9999',
                Rule::unique('jornadas')->where('torneo_id', $request->torneo_id),
            ],
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'nullable|date|after_or_equal:fecha_inicio',
            'estado'       => 'required|in:'.implode(',', array_keys($constants['estados_jornada'] ?? [])),
            'descripcion'  => 'nullable|string|max:2000',
        ], [], [
            'torneo_id'    => 'torneo',
            'nombre'       => 'nombre',
            'numero'       => 'número',
            'fecha_inicio' => 'fecha de inicio',
            'fecha_fin'    => 'fecha de fin',
            'estado'       => 'estado',
            'descripcion'  => 'descripción',
        ]);

        Jornada::create($validated);

        return redirect()->route('jornadas.index')
            ->with('success', 'Jornada creada correctamente.');
    }

    public function edit(Jornada $jornada)
    {
        $this->authorize(PermissionEnum::MATCH_DAYS_UPDATE);

        $torneos = Torneo::query()
            ->select('id', 'nombre')
            ->orderBy('nombre')
            ->get();

        return Inertia::render('Jornadas/Edit', [
            'jornada'    => $jornada,
            'torneos'    => $torneos,
            'constantes' => [
                'estados_jornada' => config('constants.estados_jornada', []),
            ],
        ]);
    }

    public function update(Request $request, Jornada $jornada): RedirectResponse
    {
        $this->authorize(PermissionEnum::MATCH_DAYS_UPDATE);

        $tenantId = auth()->user()->tenant_id;
        $constants = config('constants');

        $validated = $request->validate([
            'torneo_id'   => [
                'required',
                'integer',
                Rule::exists('torneos', 'id')->where('tenant_id', $tenantId),
            ],
            'nombre'      => 'required|string|max:255',
            'numero'      => [
                'required',
                'integer',
                'min:1',
                'max:9999',
                Rule::unique('jornadas')->where('torneo_id', $request->torneo_id)->ignore($jornada->id),
            ],
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'nullable|date|after_or_equal:fecha_inicio',
            'estado'       => 'required|in:'.implode(',', array_keys($constants['estados_jornada'] ?? [])),
            'descripcion'  => 'nullable|string|max:2000',
        ], [], [
            'torneo_id'    => 'torneo',
            'nombre'       => 'nombre',
            'numero'       => 'número',
            'fecha_inicio' => 'fecha de inicio',
            'fecha_fin'    => 'fecha de fin',
            'estado'       => 'estado',
            'descripcion'  => 'descripción',
        ]);

        $jornada->update($validated);

        return redirect()->route('jornadas.index')
            ->with('success', 'Jornada actualizada correctamente.');
    }

    public function destroy(Jornada $jornada): RedirectResponse
    {
        $this->authorize(PermissionEnum::MATCH_DAYS_DELETE);

        $jornada->delete();

        return redirect()->route('jornadas.index')
            ->with('success', 'Jornada eliminada correctamente.');
    }
}
