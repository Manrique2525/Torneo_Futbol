<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartidoSustitucion extends Model
{
    use BelongsToTenant;

    protected $table = 'partido_sustituciones';

    protected $fillable = [
        'partido_id',
        'equipo_original_id',
        'equipo_sustituto_id',
        'motivo',
        'tipo_resolucion',
        'partido_reprogramado_id',
        'notas',
        'created_by',
    ];

    public function partido(): BelongsTo
    {
        return $this->belongsTo(Partido::class, 'partido_id');
    }

    public function equipoOriginal(): BelongsTo
    {
        return $this->belongsTo(TorneoEquipo::class, 'equipo_original_id');
    }

    public function equipoSustituto(): BelongsTo
    {
        return $this->belongsTo(TorneoEquipo::class, 'equipo_sustituto_id');
    }

    public function partidoReprogramado(): BelongsTo
    {
        return $this->belongsTo(Partido::class, 'partido_reprogramado_id');
    }

    public function creadoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
