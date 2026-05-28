<?php

namespace App\Models;

use App\Enums\TorneoEquipoEstadoEnum;
use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TorneoEquipo extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $table = 'torneo_equipos';

    protected $fillable = [
        'tenant_id',
        'torneo_id',
        'team_id',
        'torneo_grupo_id',
        'seed',
        'estado',
        'fair_play_points',
        'inscrito_at',
        'aprobado_at',
        'aprobado_por',
        'rechazado_at',
        'motivo_rechazo',
        'retirado_at',
        'notas',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'seed'              => 'integer',
            'fair_play_points'  => 'decimal:2',
            'inscrito_at'       => 'datetime',
            'aprobado_at'       => 'datetime',
            'rechazado_at'      => 'datetime',
            'retirado_at'       => 'datetime',
            'metadata'          => 'array',
        ];
    }

    // ── Scopes ──────────────────────────────────────

    public function scopeAprobados($query)
    {
        return $query->where('estado', TorneoEquipoEstadoEnum::APROBADO->value);
    }

    public function scopePendientes($query)
    {
        return $query->where('estado', TorneoEquipoEstadoEnum::PENDIENTE->value);
    }

    public function scopeOrdenadosPorSeed($query)
    {
        return $query->orderByRaw('seed IS NULL, seed ASC');
    }

    // ── Relationships ───────────────────────────────

    public function torneo(): BelongsTo
    {
        return $this->belongsTo(Torneo::class);
    }

    public function equipo(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function grupo(): BelongsTo
    {
        return $this->belongsTo(TorneoGrupo::class, 'torneo_grupo_id');
    }

    public function aprobadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'aprobado_por');
    }

    public function partidosLocal(): HasMany
    {
        return $this->hasMany(Partido::class, 'equipo_local_id');
    }

    public function partidosVisitante(): HasMany
    {
        return $this->hasMany(Partido::class, 'equipo_visitante_id');
    }

    // ── Helpers ─────────────────────────────────────

    public function ocupaCupo(): bool
    {
        return in_array($this->estado, TorneoEquipoEstadoEnum::cupoOcupante(), true);
    }
}
