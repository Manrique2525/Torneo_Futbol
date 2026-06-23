<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class InscripcionPago extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'inscripcion_pagos';

    const ESTADO_PENDIENTE = 'pendiente';
    const ESTADO_CONFIRMADO = 'confirmado';
    const ESTADO_RECHAZADO = 'rechazado';

    const METODO_EFECTIVO = 'efectivo';
    const METODO_TRANSFERENCIA = 'transferencia';

    protected $fillable = [
        'tenant_id',
        'torneo_equipo_id',
        'torneo_id',
        'team_id',
        'monto',
        'moneda',
        'metodo_pago',
        'comprobante_path',
        'comprobante_original',
        'referencia',
        'estado',
        'confirmado_por',
        'confirmado_at',
        'notas',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'confirmado_at' => 'datetime',
        ];
    }

    public function torneoEquipo(): BelongsTo
    {
        return $this->belongsTo(TorneoEquipo::class, 'torneo_equipo_id');
    }

    public function torneo(): BelongsTo
    {
        return $this->belongsTo(Torneo::class);
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function confirmadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmado_por');
    }
}
