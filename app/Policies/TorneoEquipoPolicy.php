<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\Team;
use App\Models\Torneo;
use App\Models\TorneoEquipo;
use App\Models\User;

class TorneoEquipoPolicy
{
  public function viewAny(User $user): bool
  {
    return $user->can(PermissionEnum::TOURNAMENTS_VIEW);
  }

  public function view(User $user, TorneoEquipo $inscripcion): bool
  {
    return $user->can(PermissionEnum::TOURNAMENTS_VIEW);
  }

  public function create(User $user, Torneo $torneo): bool
  {
    if ($user->can(PermissionEnum::TOURNAMENTS_UPDATE)) {
      return true;
    }

    return $user->can(PermissionEnum::TEAMS_UPDATE_OWN) && $torneo->inscripcion_abierta;
  }

  /**
   * Delegado inscribiendo su propio equipo.
   */
  public function createForTeam(User $user, Torneo $torneo, Team $team): bool
  {
    if ($user->can(PermissionEnum::TOURNAMENTS_UPDATE)) {
      return true;
    }

    return $user->can(PermissionEnum::TEAMS_UPDATE_OWN)
      && $torneo->inscripcion_abierta
      && $team->delegado_id === $user->id;
  }

  public function update(User $user, TorneoEquipo $inscripcion): bool
  {
    return $user->can(PermissionEnum::TOURNAMENTS_UPDATE);
  }

  public function approve(User $user, TorneoEquipo $inscripcion): bool
  {
    return $user->can(PermissionEnum::TOURNAMENTS_UPDATE);
  }

  public function reject(User $user, TorneoEquipo $inscripcion): bool
  {
    return $user->can(PermissionEnum::TOURNAMENTS_UPDATE);
  }

  public function assignGrupo(User $user, TorneoEquipo $inscripcion): bool
  {
    return $user->can(PermissionEnum::TOURNAMENTS_UPDATE);
  }

  public function assignSeed(User $user, TorneoEquipo $inscripcion): bool
  {
    return $user->can(PermissionEnum::TOURNAMENTS_UPDATE);
  }
}
