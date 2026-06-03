<?php

namespace App\Models;

use App\Enums\PartidoEventoTipoEnum;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartidoEvento extends Model
{
    use BelongsToTenant;

    protected $table = 'partido_eventos';

    protected $fillable = [
        'tenant_id',
        'partido_id',
        'equipo_id',
        'jugador_id',
        'jugador_relacionado_id',
        'tipo',
        'minuto',
        'comentario',
    ];

    protected function casts(): array
    {
        return [
            'minuto' => 'integer',
            'tipo' => PartidoEventoTipoEnum::class,
        ];
    }

    public function partido(): BelongsTo
    {
        return $this->belongsTo(Partido::class);
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'equipo_id');
    }

    public function jugador(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'jugador_id');
    }

    public function jugadorRelacionado(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'jugador_relacionado_id');
    }
}
