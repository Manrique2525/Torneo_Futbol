<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSustitucionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'equipo_original_id' => 'required|integer|exists:torneo_equipos,id',
            'equipo_sustituto_id' => 'required|integer|exists:torneo_equipos,id|different:equipo_original_id',
            'motivo' => 'required|string|in:clima,no_presentacion,no_asistencia,otro',
            'tipo_resolucion' => 'required|string|in:reprogramado,doble_jornada',
            'nueva_fecha' => 'required_if:tipo_resolucion,reprogramado|nullable|date|after_or_equal:today',
            'nueva_hora' => 'required_if:tipo_resolucion,reprogramado|nullable|date_format:H:i',
            'cancha_id' => 'nullable|integer|exists:canchas,id',
            'arbitro_id' => 'nullable|integer|exists:arbitros,id',
            'notas' => 'nullable|string|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'equipo_original_id.required' => 'Debe seleccionar el equipo original.',
            'equipo_sustituto_id.required' => 'Debe seleccionar el equipo sustituto.',
            'equipo_sustituto_id.different' => 'El equipo sustituto debe ser diferente al original.',
            'motivo.required' => 'Debe seleccionar un motivo.',
            'motivo.in' => 'El motivo seleccionado no es válido.',
            'tipo_resolucion.required' => 'Debe seleccionar el tipo de resolución.',
            'tipo_resolucion.in' => 'El tipo de resolución no es válido.',
            'nueva_fecha.required_if' => 'La nueva fecha es requerida para reprogramación.',
            'nueva_hora.required_if' => 'La nueva hora es requerida para reprogramación.',
        ];
    }
}
