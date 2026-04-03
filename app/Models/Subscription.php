<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Subscription extends Model
{
    use HasFactory;

    protected $table = 'subscriptions';

    protected $fillable = [
        'tenant_id',
        'plan_id',

        'status',
        'billing_cycle',

        'price_paid',
        'discount_amount',
        'discount_reason',

        'starts_at',
        'ends_at',
        'trial_ends_at',
        'next_billing_at',

        'auto_renew',
        'payment_method',
        'external_id',

        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        // Money
        'price_paid'      => 'float',
        'discount_amount' => 'float',

        // Dates
        'starts_at'        => 'date',
        'ends_at'          => 'date',
        'trial_ends_at'    => 'date',
        'next_billing_at'  => 'date',
        'cancelled_at'     => 'datetime',

        // Flags
        'auto_renew' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */

    // Status
    const STATUS_TRIAL     = 'trial';
    const STATUS_ACTIVE    = 'active';
    const STATUS_PAST_DUE  = 'past_due';
    const STATUS_SUSPENDED = 'suspended';
    const STATUS_CANCELLED = 'cancelled';

    // Billing cycle
    const BILLING_MONTHLY = 'monthly';
    const BILLING_ANNUAL  = 'annual';

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
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

    public function scopeValid($query)
    {
        return $query->whereIn('status', [
            self::STATUS_ACTIVE,
            self::STATUS_TRIAL,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (Status checks)
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

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function isPastDue(): bool
    {
        return $this->status === self::STATUS_PAST_DUE;
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (Business logic)
    |--------------------------------------------------------------------------
    */

    public function isValid(): bool
    {
        return in_array($this->status, [
            self::STATUS_ACTIVE,
            self::STATUS_TRIAL,
        ]);
    }

    public function isExpired(): bool
    {
        return $this->ends_at && $this->ends_at->isPast();
    }

    public function onTrial(): bool
    {
        return $this->trial_ends_at && now()->lessThanOrEqualTo($this->trial_ends_at);
    }

    public function daysLeft(): ?int
    {
        if (!$this->ends_at) return null;

        return now()->diffInDays($this->ends_at, false);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers (Pricing)
    |--------------------------------------------------------------------------
    */

    public function getFinalPriceAttribute(): float
    {
        return max(0, $this->price_paid - $this->discount_amount);
    }

    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    public function cancel(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancelled_at' => now(),
            'cancellation_reason' => $reason,
            'auto_renew' => false,
        ]);
    }

    public function suspend(): void
    {
        $this->update([
            'status' => self::STATUS_SUSPENDED,
        ]);
    }

    public function activate(): void
    {
        $this->update([
            'status' => self::STATUS_ACTIVE,
        ]);
    }
}
