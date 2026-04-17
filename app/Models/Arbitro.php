<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Arbitro extends Model
{
    use HasFactory;

    protected $table = 'arbitros';

    protected $fillable = [
        'tenant_id',
        'nombre',
        'telefono',
        'email',
        'nivel',
        'disponible',
        'pago_por_partido',
    ];

    protected $casts = [
        'disponible' => 'boolean',
        'pago_por_partido' => 'decimal:2',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}