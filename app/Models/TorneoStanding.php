<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TorneoStanding extends Model
{
    use BelongsToTenant;

    protected $table = 'torneo_standings';

    protected $fillable = [
        'tenant_id',
        'torneo_id',
        'torneo_grupo_id',
        'torneo_equipo_id',
        'pj',
        'pg',
        'pe',
        'pp',
        'gf',
        'gc',
        'dg',
        'pts',
        'fair_play',
        'posicion',
    ];

    protected function casts(): array
    {
        return [
            'pj'       => 'integer',
            'pg'       => 'integer',
            'pe'       => 'integer',
            'pp'       => 'integer',
            'gf'       => 'integer',
            'gc'       => 'integer',
            'dg'       => 'integer',
            'pts'      => 'integer',
            'fair_play'=> 'decimal:2',
            'posicion' => 'integer',
        ];
    }

    public function torneo(): BelongsTo
    {
        return $this->belongsTo(Torneo::class);
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(TorneoGrupo::class, 'torneo_grupo_id');
    }

    public function torneoEquipo(): BelongsTo
    {
        return $this->belongsTo(TorneoEquipo::class, 'torneo_equipo_id');
    }
}
