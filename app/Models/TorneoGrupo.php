<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TorneoGrupo extends Model
{
    use BelongsToTenant;

    protected $table = 'torneo_grupos';

    protected $fillable = [
        'tenant_id',
        'torneo_id',
        'nombre',
        'orden',
    ];

    protected function casts(): array
    {
        return [
            'orden' => 'integer',
        ];
    }

    public function torneo(): BelongsTo
    {
        return $this->belongsTo(Torneo::class);
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(TorneoEquipo::class, 'torneo_grupo_id');
    }
}
