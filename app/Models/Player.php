<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Player extends Model
{
    use HasFactory, BelongsToTenant;

    const ESTADO_ACTIVO = 'activo';
    const ESTADO_SUSPENDIDO = 'suspendido';
    const ESTADO_LESIONADO = 'lesionado';

    protected $fillable = [
        'tenant_id',
        'equipo_id',
        'nombre',
        'numero',
        'posicion',
        'edad',
        'curp',
        'foto',
        'estado',
    ];

    protected $casts = [
        'numero' => 'integer',
        'edad' => 'integer',
        'estado' => 'string',
    ];

    // ── Scopes
    public function scopeActivo($query)
    {
        return $query->where('estado', self::ESTADO_ACTIVO);
    }

    public function scopeByEquipo($query, $equipoId)
    {
        return $query->where('equipo_id', $equipoId);
    }

    // ── Relaciones
    public function equipo()
    {
        return $this->belongsTo(Team::class, 'equipo_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    // ── Accessors
    public function getFotoUrlAttribute()
    {
        return $this->foto ? '/storage/' . $this->foto : null;
    }
}