<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Plan extends Model
{
    use HasFactory;

    protected $table = 'plans';

    protected $fillable = [
        'name',
        'slug',
        'description',

        // Pricing
        'monthly_price',
        'annual_price',
        'currency',

        // Limits
        'max_tournaments',
        'max_teams',
        'max_players',
        'max_users',
        'max_fields',
        'max_referees',
        'storage_mb',

        // Features
        'has_mobile_app',
        'has_streaming',
        'has_advanced_stats',
        'has_api_access',
        'has_whatsapp',
        'has_reports',
        'has_custom_domain',

        // Support
        'support_level',

        'sort_order',
        'is_active',
        'is_featured',
    ];

    protected $casts = [
        // Prices
        'monthly_price' => 'float',
        'annual_price'  => 'float',

        // Limits
        'max_tournaments' => 'integer',
        'max_teams'       => 'integer',
        'max_players'     => 'integer',
        'max_users'       => 'integer',
        'max_fields'      => 'integer',
        'max_referees'    => 'integer',
        'storage_mb'      => 'integer',

        // Features
        'has_mobile_app'     => 'boolean',
        'has_streaming'      => 'boolean',
        'has_advanced_stats' => 'boolean',
        'has_api_access'     => 'boolean',
        'has_whatsapp'       => 'boolean',
        'has_reports'        => 'boolean',
        'has_custom_domain'  => 'boolean',

        // Flags
        'is_active'   => 'boolean',
        'is_featured' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */

    const SUPPORT_BASIC     = 'basic';
    const SUPPORT_PRIORITY  = 'priority';
    const SUPPORT_DEDICATED = 'dedicated';

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (Limits)
    |--------------------------------------------------------------------------
    */

    public function isUnlimited(int $value): bool
    {
        return $value === -1;
    }

    public function hasLimit(string $field): bool
    {
        return !$this->isUnlimited($this->{$field});
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (Features)
    |--------------------------------------------------------------------------
    */

    public function hasFeature(string $feature): bool
    {
        return (bool) $this->{$feature};
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (Pricing)
    |--------------------------------------------------------------------------
    */

    public function getMonthlyPriceFormattedAttribute(): string
    {
        return number_format($this->monthly_price, 2) . ' ' . $this->currency;
    }

    public function getAnnualPriceFormattedAttribute(): string
    {
        return number_format($this->annual_price, 2) . ' ' . $this->currency;
    }
}
