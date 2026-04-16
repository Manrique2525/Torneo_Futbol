<?php

namespace App\Services;

use App\Models\Tenant;

class PlanService
{
    protected Tenant $tenant;

    public function __construct(Tenant $tenant)
    {
        $this->tenant = $tenant;
    }

    /*
    |--------------------------------------------------------------------------
    | Plan
    |--------------------------------------------------------------------------
    */

    public function hasActivePlan(): bool
    {
        return $this->tenant->isActive()
            && $this->tenant->planRelation !== null;
    }

    public function plan()
    {
        return $this->tenant->planRelation;
    }

    /*
    |--------------------------------------------------------------------------
    | Limits
    |--------------------------------------------------------------------------
    */

    public function getLimit(string $field): ?int
    {
        if (!$this->hasActivePlan()) {
            return null;
        }

        return $this->plan()->{$field};
    }

    public function isUnlimited(string $field): bool
    {
        return $this->getLimit($field) === -1;
    }

    public function canCreate(string $field, int $currentCount): bool
    {
        $limit = $this->getLimit($field);

        if (is_null($limit)) {
            return false;
        }

        if ($limit === -1) {
            return true;
        }

        return $currentCount < $limit;
    }

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    */

    public function hasFeature(string $feature): bool
    {
        if (!$this->hasActivePlan()) {
            return false;
        }

        return (bool) $this->plan()->{$feature};
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers específicos (listos para usar)
    |--------------------------------------------------------------------------
    */

    public function canCreateUser(): bool
    {
        $count = $this->tenant->users()->count();

        return $this->canCreate('max_users', $count);
    }

    public function canCreateTeam(int $teamsCount): bool
    {
        return $this->canCreate('max_teams', $teamsCount);
    }

    public function canCreateTournament(int $tournamentsCount): bool
    {
        return $this->canCreate('max_tournaments', $tournamentsCount);
    }

    public function canCreatePlayer(int $playersCount): bool
    {
        return $this->canCreate('max_players', $playersCount);
    }

    public function canCreateReferee(int $refereesCount): bool
    {
        return $this->canCreate('max_referees', $refereesCount);
    }

    /*
    |--------------------------------------------------------------------------
    | Info útil (opcional)
    |--------------------------------------------------------------------------
    */

    public function remaining(string $field, int $currentCount): ?int
    {
        $limit = $this->getLimit($field);

        if (is_null($limit) || $limit === -1) {
            return null;
        }

        return max(0, $limit - $currentCount);
    }
}