<?php

namespace App\Http\Controllers;

use App\Enums\PermissionEnum;
use App\Http\Requests\StoreInscripcionPagoRequest;
use App\Models\InscripcionPago;
use App\Models\Torneo;
use App\Models\TorneoEquipo;
use App\Services\InscripcionPagoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class InscripcionPagoController extends Controller
{
    public function __construct(
        private readonly InscripcionPagoService $pagoService
    ) {}

    public function index(Torneo $torneo): Response
    {
        $user = auth()->user();

        if (! $user->can(PermissionEnum::PAYMENTS_VIEW) && ! $user->can(PermissionEnum::PAYMENTS_UPLOAD_RECEIPT)) {
            abort(403);
        }

        $pagos = $this->pagoService->pagosDelTorneo($torneo, $user);

        // Group by equipo for easier frontend rendering
        $pagosPorEquipo = $pagos->groupBy('team_id');

        $inscripciones = $torneo->inscripciones()
            ->with(['equipo:id,name', 'pagos'])
            ->whereIn('estado', ['aprobado', 'pendiente'])
            ->orderBy('team_id')
            ->when($user->hasRole('delegate'), function ($q) use ($user) {
                $teamIds = $user->equiposComoDelegado()->pluck('id');
                $q->whereIn('team_id', $teamIds);
            })
            ->get()
            ->map(function (TorneoEquipo $inscripcion) {
                $pagos = $inscripcion->pagos;
                $ultimoPago = $pagos->sortByDesc('id')->first();

                return [
                    'id' => $inscripcion->id,
                    'equipo' => $inscripcion->equipo?->only(['id', 'name']),
                    'tienePagoPendiente' => $pagos->contains('estado', 'pendiente'),
                    'tienePagoConfirmado' => $pagos->contains('estado', 'confirmado'),
                    'ultimoPago' => $ultimoPago ? array_merge($ultimoPago->only([
                        'id', 'monto', 'metodo_pago', 'estado', 'referencia',
                        'comprobante_original', 'confirmado_at',
                    ]), [
                        'comprobante_url' => $ultimoPago->comprobante_path
                            ? Storage::url($ultimoPago->comprobante_path)
                            : null,
                    ]) : null,
                ];
            });

        return Inertia::render('Pagos/Index', [
            'torneo' => $torneo->only([
                'id', 'nombre', 'tipo', 'categoria', 'precio_inscripcion', 'moneda', 'pago_requerido',
            ]),
            'inscripciones' => $inscripciones,
            'pagosPorEquipo' => $pagosPorEquipo->map(function ($pagosEquipo, $teamId) {
                return [
                    'team_id' => (int) $teamId,
                    'pagos' => $pagosEquipo->map(fn (InscripcionPago $p) => array_merge($p->only([
                        'id', 'monto', 'metodo_pago', 'estado', 'referencia',
                        'comprobante_original', 'created_at', 'confirmado_at',
                    ]), [
                        'comprobante_url' => $p->comprobante_path
                            ? Storage::url($p->comprobante_path)
                            : null,
                    ])),
                ];
            })->values(),
            'constantes' => [
                'metodos_pago' => config('constants.metodos_pago', []),
                'estados_pago' => config('constants.estados_pago', []),
            ],
            'can' => [
                'view_payments' => auth()->user()->can(PermissionEnum::PAYMENTS_VIEW),
                'create_payments' => auth()->user()->can(PermissionEnum::PAYMENTS_CREATE),
                'upload_receipt' => auth()->user()->can(PermissionEnum::PAYMENTS_UPLOAD_RECEIPT),
            ],
        ]);
    }

    public function store(StoreInscripcionPagoRequest $request, Torneo $torneo): RedirectResponse
    {
        $user = auth()->user();

        if (! $user->can(PermissionEnum::PAYMENTS_UPLOAD_RECEIPT) || ! $user->hasRole('delegate')) {
            abort(403);
        }

        $inscripcion = TorneoEquipo::where('torneo_id', $torneo->id)
            ->where('team_id', $request->team_id)
            ->firstOrFail();

        $teamIds = $user->equiposComoDelegado()->pluck('id')->toArray();
        abort_unless(in_array($inscripcion->team_id, $teamIds), 403);

        if ($request->metodo_pago === 'efectivo') {
            $this->pagoService->registrarEfectivo($inscripcion, $request->notas);
        } else {
            $this->pagoService->registrarTransferencia(
                $inscripcion,
                $request->file('comprobante'),
                $request->referencia,
                $request->notas,
            );
        }

        return redirect()
            ->route('pagos.index', $torneo)
            ->with('success', 'Pago registrado correctamente.');
    }

    public function confirmar(Torneo $torneo, InscripcionPago $inscripcionPago): RedirectResponse
    {
        abort_unless(auth()->user()->can(PermissionEnum::PAYMENTS_CREATE), 403);

        $this->pagoService->confirmarPago($inscripcionPago, auth()->user());

        return redirect()
            ->route('pagos.index', $torneo)
            ->with('success', 'Pago confirmado correctamente.');
    }

    public function rechazar(Request $request, Torneo $torneo, InscripcionPago $inscripcionPago): RedirectResponse
    {
        abort_unless(auth()->user()->can(PermissionEnum::PAYMENTS_CREATE), 403);

        $request->validate(['motivo' => 'required|string|max:255']);

        $this->pagoService->rechazarPago($inscripcionPago, $request->motivo);

        return redirect()
            ->route('pagos.index', $torneo)
            ->with('success', 'Pago rechazado.');
    }
}
