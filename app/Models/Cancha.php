<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cancha extends Model
{
    use BelongsToTenant;

    protected $table = 'canchas';

    protected $fillable = [
        'nombre',
        'direccion',
        'tipo',
        'capacidad',
        'latitud',
        'longitud',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'capacidad' => 'integer',
            'latitud'   => 'decimal:7',
            'longitud'  => 'decimal:7',
        ];
    }

    public function disponibilidades(): HasMany
    {
        return $this->hasMany(DisponibilidadCancha::class);
    }

    public function partidos(): HasMany
    {
        return $this->hasMany(Partido::class);
    }
}
