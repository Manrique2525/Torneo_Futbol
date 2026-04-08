<?php

namespace App\Models\Traits;

use App\Contexts\TenantContext;
use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Apply this trait to EVERY model that has a tenant_id column.
 *
 * It does two things:
 * 1. AUTO-FILTER: Adds a global scope so all queries include WHERE tenant_id = ?
 * 2. AUTO-FILL:   Sets tenant_id automatically when creating a new record
 *
 * Usage:
 *   class Tournament extends Model
 *   {
 *       use BelongsToTenant;
 *   }
 *
 * After this, you NEVER need to manually add ->where('tenant_id', ...) anywhere.
 * And you NEVER need to manually set $model->tenant_id = ... when creating.
 */
trait BelongsToTenant
{
    public static function bootBelongsToTenant(): void
    {
        // ── 1. Auto-filter: scope all queries to current tenant ──
        static::addGlobalScope(new TenantScope());

        // ── 2. Auto-fill: set tenant_id on create ──
        static::creating(function ($model) {
            if (empty($model->tenant_id)) {
                $context = app(TenantContext::class);

                if ($context->has()) {
                    $model->tenant_id = $context->get();
                }
            }
        });
    }

    /**
     * Relationship to the tenant.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Check if a related model belongs to the same tenant.
     * Use this to validate foreign keys before saving.
     *
     * Example:
     *   if (!Tournament::ownedBy($request->field_id, Field::class)) {
     *       abort(403, 'This field does not belong to your organization.');
     *   }
     */
    public static function ownedBy(int $id, string $modelClass): bool
    {
        $context = app(TenantContext::class);

        if (!$context->has()) {
            return false;
        }

        return $modelClass::where('id', $id)
            ->where('tenant_id', $context->get())
            ->exists();
    }

    /**
     * Remove the tenant scope for specific queries (super admin use).
     *
     * Example:
     *   Tournament::withoutTenantScope()->get(); // all tenants
     */
    public function scopeWithoutTenantScope($query)
    {
        return $query->withoutGlobalScope(TenantScope::class);
    }
}
