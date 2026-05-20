<?php

namespace App\Http\Requests;

use App\Enums\PermissionEnum;
use App\Enums\TorneoEquipoEstadoEnum;
use App\Models\Team;
use App\Models\Torneo;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTorneoEquipoRequest extends FormRequest
{
  public function authorize(): bool
  {
    /** @var Torneo $torneo */
    $torneo = $this->route('torneo');

    if ($this->user()->can(PermissionEnum::TOURNAMENTS_UPDATE)) {
      return true;
    }

    if (! $this->user()->can(PermissionEnum::TEAMS_UPDATE_OWN)) {
      return false;
    }

    $team = Team::find($this->input('team_id'));

    return $team
      && $team->delegado_id === $this->user()->id
      && $torneo->inscripcion_abierta;
  }

  public function rules(): array
  {
    /** @var Torneo $torneo */
    $torneo = $this->route('torneo');

    return [
      'team_id' => [
        'required',
        'integer',
        Rule::exists('teams', 'id')->where('tenant_id', $torneo->tenant_id),
      ],
      'torneo_grupo_id' => [
        'nullable',
        'integer',
        Rule::exists('torneo_grupos', 'id')->where('torneo_id', $torneo->id),
      ],
      'seed' => ['nullable', 'integer', 'min:1', 'max:999'],
      'notas' => ['nullable', 'string', 'max:1000'],
      'estado' => [
        'nullable',
        'string',
        Rule::in([
          TorneoEquipoEstadoEnum::PENDIENTE->value,
          TorneoEquipoEstadoEnum::APROBADO->value,
        ]),
      ],
    ];
  }

  public function messages(): array
  {
    return [
      'team_id.exists' => 'El equipo seleccionado no es válido para esta organización.',
      'torneo_grupo_id.exists' => 'El grupo seleccionado no pertenece a este torneo.',
    ];
  }
}
