<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Jornada extends Model
{
    use BelongsToTenant;

    protected $table = 'jornadas';

    protected $fillable = [
        'torneo_id',
        'nombre',
        'numero',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'descripcion',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin'    => 'date',
            'numero'       => 'integer',
        ];
    }

    public function torneo(): BelongsTo
    {
        return $this->belongsTo(Torneo::class);
    }

    public function partidos(): HasMany
    {
        return $this->hasMany(Partido::class);
    }
}
