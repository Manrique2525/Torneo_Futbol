<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CambiarEstadoPartidoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $partido = $this->route('partido');
        $estadoActual = $partido?->estado;

        $permitidos = match ($estadoActual) {
            'programado' => ['en_juego', 'cancelado'],
            'en_juego' => ['descanso', 'finalizado', 'suspendido'],
            'descanso' => ['en_juego'],
            default => [],
        };

        return [
            'estado' => ['required', 'string', Rule::in($permitidos)],
        ];
    }

    public function attributes(): array
    {
        return [
            'estado' => 'nuevo estado',
        ];
    }
}
