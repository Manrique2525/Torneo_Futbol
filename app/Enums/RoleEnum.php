<?php

namespace App\Enums;

enum RoleEnum: string
{
    case SUPER_ADMIN = 'super_admin';
    case ADMIN       = 'admin';
    case MANAGER     = 'manager';
    case REFEREE     = 'referee';
    case DELEGATE    = 'delegate';
    case PLAYER      = 'player';

    /**
     * Human-readable labels (for UI).
     */
    public function label(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Super Admin',
            self::ADMIN       => 'Administrator',
            self::MANAGER     => 'Manager',
            self::REFEREE     => 'Referee',
            self::DELEGATE    => 'Delegate',
            self::PLAYER      => 'Player',
        };
    }

    /**
     * Description for the admin panel.
     */
    public function description(): string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Full access to the SaaS platform',
            self::ADMIN       => 'Full access within the tenant',
            self::MANAGER     => 'Manages tournaments, teams, and schedules',
            self::REFEREE     => 'Records match events and views assignments',
            self::DELEGATE    => 'Manages their own team and players',
            self::PLAYER      => 'Views schedules and confirms attendance',
        };
    }

    /**
     * Roles assignable by a tenant admin (excludes super_admin).
     */
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

    /**
     * Default permissions for each role.
     * This is used by the seeder to build the role→permission matrix.
     */
    public function defaultPermissions(): array
    {
        return match ($this) {
            self::SUPER_ADMIN => ['*'], // handled by Gate::before, not actual permissions

            self::ADMIN => PermissionEnum::all(),

            self::MANAGER => [
                // Tournaments
                'tournaments.view', 'tournaments.create', 'tournaments.update',
                // Teams
                'teams.view', 'teams.create', 'teams.update',
                // Players
                'players.view', 'players.create', 'players.update',
                // Matches
                'matches.view', 'matches.create', 'matches.update', 'matches.record_events',
                // Match days
                'match_days.view', 'match_days.create', 'match_days.update',
                // Fields
                'fields.view', 'fields.create', 'fields.update',
                // Referees
                'referees.view', 'referees.create', 'referees.update',
                // Sanctions
                'sanctions.view', 'sanctions.create',
                // Payments
                'payments.view', 'payments.create',
                // Stats & standings
                'stats.view', 'standings.view', 'standings.recalculate',
                // Reports
                'reports.view',
            ],

            self::REFEREE => [
                'tournaments.view',
                'teams.view',
                'players.view',
                'matches.view', 'matches.record_events',
                'match_days.view',
                'fields.view',
                'sanctions.view',
                'stats.view', 'standings.view',
            ],

            self::DELEGATE => [
                'tournaments.view',
                'teams.view', 'teams.update_own',
                'players.view', 'players.create', 'players.update_own',
                'matches.view', 'matches.confirm',
                'match_days.view',
                'fields.view',
                'sanctions.view',
                'payments.view', 'payments.upload_receipt',
                'stats.view', 'standings.view',
            ],

            self::PLAYER => [
                'tournaments.view',
                'teams.view',
                'players.view_own',
                'matches.view', 'matches.confirm_attendance',
                'match_days.view',
                'stats.view', 'standings.view',
            ],
        };
    }
}
