<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DisponibilidadCancha extends Model
{
    use BelongsToTenant;

    protected $table = 'disponibilidad_canchas';

    protected $fillable = [
        'cancha_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
    ];

    protected function casts(): array
    {
        return [
            'dia_semana'  => 'integer',
            'hora_inicio' => 'datetime:H:i',
            'hora_fin'    => 'datetime:H:i',
        ];
    }

    public function cancha(): BelongsTo
    {
        return $this->belongsTo(Cancha::class);
    }
}
