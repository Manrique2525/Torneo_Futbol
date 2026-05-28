<?php

namespace App\Policies;

use App\Enums\PermissionEnum;
use App\Models\Partido;
use App\Models\User;

class PartidoPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(PermissionEnum::MATCHES_VIEW);
    }

    public function view(User $user, Partido $partido): bool
    {
        return $user->can(PermissionEnum::MATCHES_VIEW);
    }

    public function create(User $user): bool
    {
        return $user->can(PermissionEnum::MATCHES_CREATE);
    }

    public function update(User $user, Partido $partido): bool
    {
        return $user->can(PermissionEnum::MATCHES_UPDATE);
    }

    public function delete(User $user, Partido $partido): bool
    {
        return $user->can(PermissionEnum::MATCHES_DELETE);
    }
}
