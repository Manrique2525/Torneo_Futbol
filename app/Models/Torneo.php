<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class Torneo extends Model {

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
        'created_by'
    ];

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
