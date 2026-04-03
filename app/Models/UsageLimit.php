<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UsageLimit extends Model
{
    use HasFactory;

    protected $table = 'usage_limits';

    protected $fillable = [
        'tenant_id',
        'active_tournaments',
        'total_teams',
        'total_players',
        'total_users',
        'storage_used_mb',
        'api_requests_month',
        'last_reset_at',
    ];

    protected $casts = [
        'active_tournaments' => 'integer',
        'total_teams'        => 'integer',
        'total_players'      => 'integer',
        'total_users'        => 'integer',
        'storage_used_mb'    => 'integer',
        'api_requests_month' => 'integer',
        'last_reset_at'      => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (increment / decrement)
    |--------------------------------------------------------------------------
    */

    public function incrementTeams(int $amount = 1): void
    {
        $this->increment('total_teams', $amount);
    }

    public function decrementTeams(int $amount = 1): void
    {
        $this->decrement('total_teams', $amount);
    }

    public function incrementPlayers(int $amount = 1): void
    {
        $this->increment('total_players', $amount);
    }

    public function incrementUsers(int $amount = 1): void
    {
        $this->increment('total_users', $amount);
    }

    public function incrementTournaments(int $amount = 1): void
    {
        $this->increment('active_tournaments', $amount);
    }

    public function incrementApiUsage(int $amount = 1): void
    {
        $this->increment('api_requests_month', $amount);
    }

    public function addStorage(int $mb): void
    {
        $this->increment('storage_used_mb', $mb);
    }

    public function reduceStorage(int $mb): void
    {
        $this->decrement('storage_used_mb', $mb);
    }

    /*
    |--------------------------------------------------------------------------
    | Reset methods
    |--------------------------------------------------------------------------
    */

    public function resetApiUsage(): void
    {
        $this->update([
            'api_requests_month' => 0,
            'last_reset_at' => now(),
        ]);
    }
}
