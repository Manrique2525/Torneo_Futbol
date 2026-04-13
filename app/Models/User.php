<?php

namespace App\Models;

use App\Models\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes, HasRoles, BelongsToTenant;

    const STATUS_ACTIVE    = 'active';
    const STATUS_INACTIVE  = 'inactive';
    const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'phone',
        'password',
        'avatar',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // ── Scopes ──────────────────────────────────────
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    // ── Helpers ─────────────────────────────────────
    public function isSuperAdmin(): bool
    {
        return $this->hasRole(\App\Enums\RoleEnum::SUPER_ADMIN->value);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(\App\Enums\RoleEnum::ADMIN->value);
    }

    // ── Relationships ───────────────────────────────
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
