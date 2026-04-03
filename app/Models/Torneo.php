<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Torneo extends Model
{
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