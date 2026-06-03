<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePartidoAsistenciaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'asistencias' => ['required', 'array'],
            'asistencias.*.jugador_id' => ['required', 'integer', 'exists:players,id'],
            'asistencias.*.equipo_id' => ['required', 'integer', 'exists:teams,id'],
            'asistencias.*.asistio_primera_mitad' => ['nullable', 'boolean'],
            'asistencias.*.asistio_segunda_mitad' => ['nullable', 'boolean'],
        ];
    }

    public function attributes(): array
    {
        return [
            'asistencias' => 'asistencias',
            'asistencias.*.jugador_id' => 'jugador',
            'asistencias.*.equipo_id' => 'equipo',
            'asistencias.*.asistio_primera_mitad' => 'asistencia primera mitad',
            'asistencias.*.asistio_segunda_mitad' => 'asistencia segunda mitad',
        ];
    }
}
