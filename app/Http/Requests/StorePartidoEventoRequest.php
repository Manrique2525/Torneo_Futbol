<?php

namespace App\Http\Requests;

use App\Enums\PartidoEventoTipoEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePartidoEventoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $partido = $this->route('partido');
        $maxMinuto = $partido ? $partido->duracion_minutos + 15 : 300;

        return [
            'equipo_id' => ['required', 'integer', 'exists:teams,id'],
            'jugador_id' => [
                'nullable',
                'integer',
                'exists:players,id',
                Rule::requiredIf(function () {
                    $tipo = $this->input('tipo');
                    return in_array($tipo, [
                        PartidoEventoTipoEnum::GOL->value,
                        PartidoEventoTipoEnum::AUTOGOL->value,
                        PartidoEventoTipoEnum::GOL_PENAL->value,
                        PartidoEventoTipoEnum::TARJETA_AMARILLA->value,
                        PartidoEventoTipoEnum::TARJETA_ROJA->value,
                        PartidoEventoTipoEnum::FALTA->value,
                        PartidoEventoTipoEnum::SUSTITUCION_ENTRADA->value,
                        PartidoEventoTipoEnum::SUSTITUCION_SALIDA->value,
                    ], true);
                }),
            ],
            'jugador_relacionado_id' => [
                'nullable',
                'integer',
                'exists:players,id',
                Rule::requiredIf(function () {
                    $tipo = $this->input('tipo');
                    return in_array($tipo, [
                        PartidoEventoTipoEnum::SUSTITUCION_ENTRADA->value,
                        PartidoEventoTipoEnum::SUSTITUCION_SALIDA->value,
                    ], true);
                }),
            ],
            'tipo' => ['required', 'string', Rule::in(array_column(PartidoEventoTipoEnum::cases(), 'value'))],
            'minuto' => ['required', 'integer', 'min:1', "max:$maxMinuto"],
            'comentario' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'equipo_id' => 'equipo',
            'jugador_id' => 'jugador',
            'jugador_relacionado_id' => 'jugador relacionado',
            'tipo' => 'tipo de evento',
            'minuto' => 'minuto',
            'comentario' => 'comentario',
        ];
    }
}
