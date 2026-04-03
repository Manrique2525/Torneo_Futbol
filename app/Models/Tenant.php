<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tenants';

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'custom_domain',

        // Branding
        'logo',
        'email',
        'phone',
        'address',

        // Localization
        'country',
        'timezone',
        'locale',
        'currency',

        'status',
        'last_activity_at',
        'settings',
    ];

    protected $casts = [
        'settings'          => 'array',
        'last_activity_at'  => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Boot (auto UUID)
    |--------------------------------------------------------------------------
    */

    protected static function booted()
    {
        static::creating(function ($tenant) {
            if (empty($tenant->uuid)) {
                $tenant->uuid = (string) Str::uuid();
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */

    const STATUS_ACTIVE    = 'active';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_TRIAL     = 'trial';

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function usage()
    {
        return $this->hasOne(UsageLimit::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    // 👉 Suscripción actual (muy importante)
    public function subscription()
    {
        return $this->hasOne(Subscription::class)->latestOfMany();
    }

    // 👉 Plan actual (atajo)
    public function plan()
    {
        return $this->hasOneThrough(
            Plan::class,
            Subscription::class,
            'tenant_id', // FK en subscriptions
            'id',        // PK en plans
            'id',        // PK en tenants
            'plan_id'    // FK en subscriptions hacia plans
        )->latestOfMany();
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeTrial($query)
    {
        return $query->where('status', self::STATUS_TRIAL);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (Status)
    |--------------------------------------------------------------------------
    */

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isTrial(): bool
    {
        return $this->status === self::STATUS_TRIAL;
    }

    public function isSuspended(): bool
    {
        return $this->status === self::STATUS_SUSPENDED;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (Subscription)
    |--------------------------------------------------------------------------
    */

    public function hasValidSubscription(): bool
    {
        return $this->subscription && $this->subscription->isValid();
    }

    public function currentPlan(): ?Plan
    {
        return $this->subscription?->plan;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (Usage)
    |--------------------------------------------------------------------------
    */

    public function hasReachedLimit(string $feature, int $used): bool
    {
        $plan = $this->currentPlan();

        if (!$plan) return true;

        $limit = $plan->{$feature};

        if ($limit === -1) return false;

        return $used >= $limit;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (Settings)
    |--------------------------------------------------------------------------
    */

    public function getSetting(string $key, $default = null)
    {
        return data_get($this->settings, $key, $default);
    }

    public function setSetting(string $key, $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);

        $this->update([
            'settings' => $settings
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (Activity)
    |--------------------------------------------------------------------------
    */

    public function updateLastActivity(): void
    {
        $this->update([
            'last_activity_at' => now()
        ]);
    }
}
