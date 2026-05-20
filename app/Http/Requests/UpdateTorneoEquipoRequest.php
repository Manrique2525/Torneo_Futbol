<?php

namespace App\Http\Requests;

use App\Models\Torneo;
use App\Models\TorneoEquipo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTorneoEquipoRequest extends FormRequest
{
  public function authorize(): bool
  {
    return $this->user()->can('update', $this->route('torneoEquipo'));
  }

  public function rules(): array
  {
    /** @var Torneo $torneo */
    $torneo = $this->route('torneo');

    /** @var TorneoEquipo $inscripcion */
    $inscripcion = $this->route('torneoEquipo');

    return [
      'torneo_grupo_id' => [
        'sometimes',
        'nullable',
        'integer',
        Rule::exists('torneo_grupos', 'id')->where('torneo_id', $torneo->id),
      ],
      'seed' => [
        'sometimes',
        'nullable',
        'integer',
        'min:1',
        'max:999',
        Rule::unique('torneo_equipos', 'seed')
          ->where('torneo_id', $torneo->id)
          ->ignore($inscripcion->id),
      ],
      'notas' => ['sometimes', 'nullable', 'string', 'max:1000'],
    ];
  }

  public function messages(): array
  {
    return [
      'seed.unique' => 'Ese seed ya está asignado a otro equipo en este torneo.',
      'torneo_grupo_id.exists' => 'El grupo no pertenece a este torneo.',
    ];
  }
}
