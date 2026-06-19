<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partido extends Model
{
    use BelongsToTenant;

    protected $table = 'partidos';

    protected $fillable = [
        'torneo_id',
        'jornada_id',
        'equipo_local_id',
        'equipo_visitante_id',
        'cancha_id',
        'arbitro_id',
        'fecha',
        'hora',
        'duracion_minutos',
        'estado',
        'fase',
        'es_vuelta',
        'llave_bracket',
        'orden_bracket',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'hora' => 'datetime:H:i',
            'duracion_minutos' => 'integer',
            'goles_local' => 'integer',
            'goles_visitante' => 'integer',
            'es_vuelta' => 'boolean',
            'orden_bracket' => 'integer',
        ];
    }

    public function torneo(): BelongsTo
    {
        return $this->belongsTo(Torneo::class);
    }

    public function jornada(): BelongsTo
    {
        return $this->belongsTo(Jornada::class);
    }

    public function equipoLocal(): BelongsTo
    {
        return $this->belongsTo(TorneoEquipo::class, 'equipo_local_id');
    }

    public function equipoVisitante(): BelongsTo
    {
        return $this->belongsTo(TorneoEquipo::class, 'equipo_visitante_id');
    }

    public function cancha(): BelongsTo
    {
        return $this->belongsTo(Cancha::class);
    }

    public function arbitro(): BelongsTo
    {
        return $this->belongsTo(Arbitro::class);
    }

    public function eventos(): HasMany
    {
        return $this->hasMany(PartidoEvento::class, 'partido_id')->orderBy('minuto')->orderBy('created_at');
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(PartidoAsistencia::class, 'partido_id');
    }

    public function sustituciones(): HasMany
    {
        return $this->hasMany(PartidoSustitucion::class, 'partido_id');
    }

    public function puedeRegistrarEventos(): bool
    {
        return in_array($this->estado, ['en_juego', 'descanso'], true);
    }
}
