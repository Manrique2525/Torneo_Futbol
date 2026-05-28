<?php

namespace App\Http\Requests;

use App\Enums\PermissionEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePartidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can(PermissionEnum::MATCHES_UPDATE);
    }

    public function rules(): array
    {
        $tenantId = $this->user()->tenant_id;

        return [
            'torneo_id' => [
                'required',
                'integer',
                Rule::exists('torneos', 'id')->where('tenant_id', $tenantId),
            ],
            'jornada_id' => [
                'nullable',
                'integer',
                Rule::exists('jornadas', 'id'),
            ],
            'equipo_local_id' => [
                'required',
                'integer',
                Rule::exists('torneo_equipos', 'id')->where('estado', 'aprobado'),
            ],
            'equipo_visitante_id' => [
                'required',
                'integer',
                'different:equipo_local_id',
                Rule::exists('torneo_equipos', 'id')->where('estado', 'aprobado'),
            ],
            'cancha_id' => [
                'nullable',
                'integer',
                Rule::exists('canchas', 'id'),
            ],
            'arbitro_id' => [
                'nullable',
                'integer',
                Rule::exists('arbitros', 'id')->where('tenant_id', $tenantId),
            ],
            'fecha'             => 'required|date',
            'hora'              => 'required|date_format:H:i',
            'duracion_minutos'  => 'required|integer|min:15|max:300',
            'estado'            => 'required|in:programado,en_juego,finalizado,suspendido,cancelado',
        ];
    }

    public function messages(): array
    {
        return [
            'equipo_visitante_id.different' => 'El equipo local y visitante no pueden ser el mismo.',
            'arbitro_id.exists'             => 'El árbitro seleccionado no pertenece a tu organización.',
            'torneo_id.exists'              => 'El torneo seleccionado no es válido.',
            'cancha_id.exists'              => 'La cancha seleccionada no es válida.',
        ];
    }
}
