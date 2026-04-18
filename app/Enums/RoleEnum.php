<?php

namespace App\Enums;

class RoleEnum
{
    public const SUPER_ADMIN = 'super_admin';
    public const ADMIN       = 'admin';
    public const MANAGER     = 'manager';
    public const REFEREE     = 'referee';
    public const DELEGATE    = 'delegate';
    public const PLAYER      = 'player';

    public static function all(): array
    {
        return [
            self::SUPER_ADMIN,
            self::ADMIN,
            self::MANAGER,
            self::REFEREE,
            self::DELEGATE,
            self::PLAYER,
        ];
    }

    public static function assignable(): array
    {
        return [
            self::ADMIN,
            self::MANAGER,
            self::REFEREE,
            self::DELEGATE,
            self::PLAYER,
        ];
    }

    public static function labels(): array
    {
        return [
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN       => 'Administrator',
            self::MANAGER     => 'Manager',
            self::REFEREE     => 'Referee',
            self::DELEGATE    => 'Delegate',
            self::PLAYER      => 'Player',
        ];
    }

    public static function descriptions(): array
    {
        return [
            self::SUPER_ADMIN => 'Full access to the SaaS platform',
            self::ADMIN       => 'Full access within the tenant',
            self::MANAGER     => 'Manages tournaments, teams, and schedules',
            self::REFEREE     => 'Records match events and views assignments',
            self::DELEGATE    => 'Manages their own team and players',
            self::PLAYER      => 'Views schedules and confirms attendance',
        ];
    }

    public static function defaultPermissions(string $role): array
    {
        return match ($role) {

            self::SUPER_ADMIN => [],

            self::ADMIN => PermissionEnum::all(),

            self::MANAGER => [
                PermissionEnum::TOURNAMENTS_VIEW,
                PermissionEnum::TOURNAMENTS_CREATE,
                PermissionEnum::TOURNAMENTS_UPDATE,

                PermissionEnum::TEAMS_VIEW,
                PermissionEnum::TEAMS_CREATE,
                PermissionEnum::TEAMS_UPDATE,

                PermissionEnum::PLAYERS_VIEW,
                PermissionEnum::PLAYERS_CREATE,
                PermissionEnum::PLAYERS_UPDATE,

                PermissionEnum::MATCHES_VIEW,
                PermissionEnum::MATCHES_CREATE,
                PermissionEnum::MATCHES_UPDATE,
                PermissionEnum::MATCHES_RECORD_EVENTS,

                PermissionEnum::MATCH_DAYS_VIEW,
                PermissionEnum::MATCH_DAYS_CREATE,
                PermissionEnum::MATCH_DAYS_UPDATE,

                PermissionEnum::FIELDS_VIEW,
                PermissionEnum::FIELDS_CREATE,
                PermissionEnum::FIELDS_UPDATE,

                PermissionEnum::REFEREES_VIEW,
                PermissionEnum::REFEREES_CREATE,
                PermissionEnum::REFEREES_UPDATE,

                PermissionEnum::SANCTIONS_VIEW,
                PermissionEnum::SANCTIONS_CREATE,

                PermissionEnum::PAYMENTS_VIEW,
                PermissionEnum::PAYMENTS_CREATE,

                PermissionEnum::STATS_VIEW,
                PermissionEnum::STANDINGS_VIEW,
                PermissionEnum::STANDINGS_RECALCULATE,

                PermissionEnum::REPORTS_VIEW,
            ],

            self::REFEREE => [
                PermissionEnum::TOURNAMENTS_VIEW,
                PermissionEnum::TEAMS_VIEW,
                PermissionEnum::PLAYERS_VIEW,
                PermissionEnum::MATCHES_VIEW,
                PermissionEnum::MATCHES_RECORD_EVENTS,
                PermissionEnum::MATCH_DAYS_VIEW,
                PermissionEnum::FIELDS_VIEW,
                PermissionEnum::SANCTIONS_VIEW,
                PermissionEnum::STATS_VIEW,
                PermissionEnum::STANDINGS_VIEW,
            ],

            self::DELEGATE => [
                PermissionEnum::TOURNAMENTS_VIEW,
                PermissionEnum::TEAMS_VIEW,
                PermissionEnum::TEAMS_UPDATE_OWN,

                PermissionEnum::PLAYERS_VIEW,
                PermissionEnum::PLAYERS_CREATE,
                PermissionEnum::PLAYERS_UPDATE_OWN,

                PermissionEnum::MATCHES_VIEW,
                PermissionEnum::MATCHES_CONFIRM,

                PermissionEnum::MATCH_DAYS_VIEW,
                PermissionEnum::FIELDS_VIEW,
                PermissionEnum::SANCTIONS_VIEW,

                PermissionEnum::PAYMENTS_VIEW,
                PermissionEnum::PAYMENTS_UPLOAD_RECEIPT,

                PermissionEnum::STATS_VIEW,
                PermissionEnum::STANDINGS_VIEW,
            ],

            self::PLAYER => [
                PermissionEnum::TOURNAMENTS_VIEW,
                PermissionEnum::TEAMS_VIEW,
                PermissionEnum::PLAYERS_VIEW_OWN,
                PermissionEnum::MATCHES_VIEW,
                PermissionEnum::MATCHES_CONFIRM_ATTENDANCE,
                PermissionEnum::MATCH_DAYS_VIEW,
                PermissionEnum::STATS_VIEW,
                PermissionEnum::STANDINGS_VIEW,
            ],
        };
    }
}
