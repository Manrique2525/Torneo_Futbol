<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends \Illuminate\Database\Eloquent\Model
{
    use HasFactory, SoftDeletes;

    const STATUS_ACTIVE    = 'active';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_TRIAL     = 'trial';

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'custom_domain',
        'logo',
        'email',
        'phone',
        'address',
        'country',
        'timezone',
        'locale',
        'currency',
        'plan',
        'status',
        'last_activity_at',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'settings'         => 'array',
            'last_activity_at' => 'datetime',
        ];
    }

    // ── Scopes ──────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->whereIn('status', [self::STATUS_ACTIVE, self::STATUS_TRIAL]);
    }

    // ── Relationships ───────────────────────────────
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    // ── Helpers ─────────────────────────────────────
    public function isActive(): bool
    {
        return in_array($this->status, [self::STATUS_ACTIVE, self::STATUS_TRIAL]);
    }

    public function planConfig(): array
    {
        return config("plans.{$this->plan}", config('plans.basic'));
    }
}
