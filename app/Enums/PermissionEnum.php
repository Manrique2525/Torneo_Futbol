<?php

namespace App\Enums;

final class PermissionEnum
{
    // Tournaments
    const TOURNAMENTS_VIEW   = 'tournaments.view';
    const TOURNAMENTS_CREATE = 'tournaments.create';
    const TOURNAMENTS_UPDATE = 'tournaments.update';
    const TOURNAMENTS_DELETE = 'tournaments.delete';

    // Teams
    const TEAMS_VIEW       = 'teams.view';
    const TEAMS_CREATE     = 'teams.create';
    const TEAMS_UPDATE     = 'teams.update';
    const TEAMS_UPDATE_OWN = 'teams.update_own';
    const TEAMS_DELETE     = 'teams.delete';

    // Players
    const PLAYERS_VIEW       = 'players.view';
    const PLAYERS_VIEW_OWN   = 'players.view_own';
    const PLAYERS_CREATE     = 'players.create';
    const PLAYERS_UPDATE     = 'players.update';
    const PLAYERS_UPDATE_OWN = 'players.update_own';
    const PLAYERS_DELETE     = 'players.delete';

    // Matches
    const MATCHES_VIEW               = 'matches.view';
    const MATCHES_CREATE             = 'matches.create';
    const MATCHES_UPDATE             = 'matches.update';
    const MATCHES_DELETE             = 'matches.delete';
    const MATCHES_RECORD_EVENTS      = 'matches.record_events';
    const MATCHES_CONFIRM            = 'matches.confirm';
    const MATCHES_CONFIRM_ATTENDANCE = 'matches.confirm_attendance';

    // Match days
    const MATCH_DAYS_VIEW   = 'match_days.view';
    const MATCH_DAYS_CREATE = 'match_days.create';
    const MATCH_DAYS_UPDATE = 'match_days.update';
    const MATCH_DAYS_DELETE = 'match_days.delete';

    // Fields
    const FIELDS_VIEW   = 'fields.view';
    const FIELDS_CREATE = 'fields.create';
    const FIELDS_UPDATE = 'fields.update';
    const FIELDS_DELETE = 'fields.delete';

    // Referees
    const REFEREES_VIEW   = 'referees.view';
    const REFEREES_CREATE = 'referees.create';
    const REFEREES_UPDATE = 'referees.update';
    const REFEREES_DELETE = 'referees.delete';

    // Sanctions
    const SANCTIONS_VIEW   = 'sanctions.view';
    const SANCTIONS_CREATE = 'sanctions.create';

    // Payments
    const PAYMENTS_VIEW           = 'payments.view';
    const PAYMENTS_CREATE         = 'payments.create';
    const PAYMENTS_UPLOAD_RECEIPT = 'payments.upload_receipt';

    // Stats
    const STATS_VIEW            = 'stats.view';
    const STANDINGS_VIEW        = 'standings.view';
    const STANDINGS_RECALCULATE = 'standings.recalculate';

    // Reports
    const REPORTS_VIEW = 'reports.view';

    // Users & roles
    const USERS_VIEW   = 'users.view';
    const USERS_CREATE = 'users.create';
    const USERS_UPDATE = 'users.update';
    const USERS_DELETE = 'users.delete';

    const ROLES_VIEW   = 'roles.view';
    const ROLES_CREATE = 'roles.create';
    const ROLES_UPDATE = 'roles.update';
    const ROLES_DELETE = 'roles.delete';


    const PRUEBA_VIEW   = 'prueba.view';
    const PRUEBA_CREATE = 'prueba.create';
    const PRUEBA_UPDATE = 'prueba.update';
    const PRUEBA_DELETE = 'prueba.delete';


    public static function all(): array
    {
        return (new \ReflectionClass(self::class))->getConstants();
    }

    public static function grouped(): array
    {
        $grouped = [];

        foreach (self::all() as $permission) {
            [$module, $action] = explode('.', $permission, 2);

            $grouped[$module][] = $permission;
        }

        return $grouped;
    }
}
