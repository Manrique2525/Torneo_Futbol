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
        'precio_inscripcion',
        'moneda',
        'pago_requerido',
        'baja_por_impago_automatica',
        'max_jornadas_sin_pago',
        'configuracion_asistencia_delegado',
        'fair_play_automatico',
        'ida_y_vuelta',
        'formato_relampago',
        'tiene_playoff',
        'playoff_equipos',
        'playoff_ida_vuelta',
        'hora_inicio',
        'duracion_minutos',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'max_equipos' => 'integer',
            'inscripcion_abierta' => 'boolean',
            'precio_inscripcion' => 'decimal:2',
            'pago_requerido' => 'boolean',
            'baja_por_impago_automatica' => 'boolean',
            'max_jornadas_sin_pago' => 'integer',
            'configuracion_asistencia_delegado' => 'boolean',
            'fair_play_automatico' => 'boolean',
            'ida_y_vuelta' => 'boolean',
            'tiene_playoff' => 'boolean',
            'playoff_equipos' => 'integer',
            'playoff_ida_vuelta' => 'boolean',
            'hora_inicio' => 'datetime:H:i',
            'duracion_minutos' => 'integer',
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

    public function pagos(): HasMany
    {
        return $this->hasMany(InscripcionPago::class);
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

    public function esLiga(): bool
    {
        return $this->tipo === 'liga';
    }

    public function esCopa(): bool
    {
        return $this->tipo === 'copa';
    }

    public function esRelampago(): bool
    {
        return $this->tipo === 'relampago';
    }

    public function tieneIdaYVuelta(): bool
    {
        return $this->ida_y_vuelta && ! $this->esRelampago();
    }

    public function tienePlayoff(): bool
    {
        return $this->tiene_playoff && $this->playoff_equipos > 0;
    }

    public static function esPotenciaDe2(int $n): bool
    {
        return $n > 0 && ($n & ($n - 1)) === 0;
    }
}
