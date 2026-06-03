<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartidoAsistencia extends Model
{
    use BelongsToTenant;

    protected $table = 'partido_asistencias';

    protected $fillable = [
        'tenant_id',
        'partido_id',
        'equipo_id',
        'jugador_id',
        'asistio_primera_mitad',
        'asistio_segunda_mitad',
    ];

    protected function casts(): array
    {
        return [
            'asistio_primera_mitad' => 'boolean',
            'asistio_segunda_mitad' => 'boolean',
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
}
