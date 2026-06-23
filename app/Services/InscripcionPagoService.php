<?php

namespace App\Services;

use App\Models\InscripcionPago;
use App\Models\Torneo;
use App\Models\TorneoEquipo;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class InscripcionPagoService
{
    public function pagosDelTorneo(Torneo $torneo, ?User $user = null)
    {
        $query = InscripcionPago::with(['equipo:id,name', 'confirmadoPor:id,name'])
            ->where('torneo_id', $torneo->id);

        if ($user && $user->hasRole('delegate')) {
            $teamIds = $user->equiposComoDelegado()->pluck('id');
            $query->whereIn('team_id', $teamIds);
        }

        return $query->latest()->get();
    }

    public function registrarTransferencia(
        TorneoEquipo $inscripcion,
        UploadedFile $comprobante,
        ?string $referencia = null,
        ?string $notas = null
    ): InscripcionPago {
        $this->assertNoPagoPendiente($inscripcion);

        $path = $comprobante->store('comprobantes', 'public');

        return DB::transaction(function () use ($inscripcion, $path, $comprobante, $referencia, $notas) {
            return InscripcionPago::create([
                'tenant_id' => $inscripcion->tenant_id,
                'torneo_equipo_id' => $inscripcion->id,
                'torneo_id' => $inscripcion->torneo_id,
                'team_id' => $inscripcion->team_id,
                'monto' => $inscripcion->torneo->precio_inscripcion ?? 0,
                'moneda' => $inscripcion->torneo->moneda ?? 'MXN',
                'metodo_pago' => InscripcionPago::METODO_TRANSFERENCIA,
                'comprobante_path' => $path,
                'comprobante_original' => $comprobante->getClientOriginalName(),
                'referencia' => $referencia,
                'estado' => InscripcionPago::ESTADO_PENDIENTE,
                'notas' => $notas,
            ]);
        });
    }

    public function registrarEfectivo(
        TorneoEquipo $inscripcion,
        ?string $notas = null
    ): InscripcionPago {
        $this->assertNoPagoPendiente($inscripcion);

        return DB::transaction(function () use ($inscripcion, $notas) {
            return InscripcionPago::create([
                'tenant_id' => $inscripcion->tenant_id,
                'torneo_equipo_id' => $inscripcion->id,
                'torneo_id' => $inscripcion->torneo_id,
                'team_id' => $inscripcion->team_id,
                'monto' => $inscripcion->torneo->precio_inscripcion ?? 0,
                'moneda' => $inscripcion->torneo->moneda ?? 'MXN',
                'metodo_pago' => InscripcionPago::METODO_EFECTIVO,
                'estado' => InscripcionPago::ESTADO_PENDIENTE,
                'notas' => $notas,
            ]);
        });
    }

    public function confirmarPago(InscripcionPago $pago, User $admin): InscripcionPago
    {
        if ($pago->estado !== InscripcionPago::ESTADO_PENDIENTE) {
            throw ValidationException::withMessages([
                'pago' => 'Solo se pueden confirmar pagos pendientes.',
            ]);
        }

        $pago->update([
            'estado' => InscripcionPago::ESTADO_CONFIRMADO,
            'confirmado_por' => $admin->id,
            'confirmado_at' => now(),
        ]);

        return $pago->fresh();
    }

    public function rechazarPago(InscripcionPago $pago, string $motivo): InscripcionPago
    {
        if ($pago->estado !== InscripcionPago::ESTADO_PENDIENTE) {
            throw ValidationException::withMessages([
                'pago' => 'Solo se pueden rechazar pagos pendientes.',
            ]);
        }

        $pago->update([
            'estado' => InscripcionPago::ESTADO_RECHAZADO,
            'notas' => $motivo,
        ]);

        if ($pago->comprobante_path) {
            Storage::disk('public')->delete($pago->comprobante_path);
        }

        return $pago->fresh();
    }

    protected function assertNoPagoPendiente(TorneoEquipo $inscripcion): void
    {
        $exists = InscripcionPago::where('torneo_equipo_id', $inscripcion->id)
            ->where('estado', InscripcionPago::ESTADO_PENDIENTE)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'pago' => 'Ya existe un pago pendiente para esta inscripción.',
            ]);
        }
    }
}
