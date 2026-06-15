<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Torneo extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'nombre',
        'tipo',
        'categoria',
        'rama',
        'fecha_inicio',
        'fecha_fin',
        'reglas',
        'estado',
        'max_equipos',
        'inscripcion_abierta',
        'configuracion_asistencia_delegado',
        'fair_play_automatico',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio'        => 'date',
            'fecha_fin'           => 'date',
            'max_equipos'         => 'integer',
            'inscripcion_abierta' => 'boolean',
            'configuracion_asistencia_delegado' => 'boolean',
            'fair_play_automatico' => 'boolean',
        ];
    }

    public function creador(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function jornadas(): HasMany
    {
        return $this->hasMany(Jornada::class);
    }

    public function partidos(): HasMany
    {
        return $this->hasMany(Partido::class);
    }

    public function grupos(): HasMany
    {
        return $this->hasMany(TorneoGrupo::class);
    }

    public function inscripciones(): HasMany
    {
        return $this->hasMany(TorneoEquipo::class);
    }

    public function equipos(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'torneo_equipos', 'torneo_id', 'team_id')
            ->withPivot([
                'id',
                'torneo_grupo_id',
                'seed',
                'estado',
                'fair_play_points',
                'inscrito_at',
                'aprobado_at',
            ])
            ->withTimestamps();
    }

    public function equiposAprobados(): BelongsToMany
    {
        return $this->equipos()->wherePivot('estado', 'aprobado');
    }

    public function standings(): HasMany
    {
        return $this->hasMany(TorneoStanding::class, 'torneo_id')->orderBy('posicion_posiciones');
    }

    public function standingsPorGrupo(?int $grupoId): HasMany
    {
        return $this->standings()->when($grupoId, fn ($q) => $q->where('torneo_grupo_id', $grupoId));
    }

    public function aceptaInscripciones(): bool
    {
        return $this->inscripcion_abierta
            && $this->estado === 'activo';
    }
}

