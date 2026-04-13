<?php

namespace App\Models\Scopes;

use App\Contexts\TenantContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Global scope that automatically adds WHERE tenant_id = ? to every query.
 *
 * Applied by the BelongsToTenant trait on any model that has a tenant_id column.
 * This ensures complete data isolation between tenants.
 */
class TenantScope implements Scope
{
    /**
     * @param  \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model>  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     */
    public function apply(Builder $builder, Model $model): void
    {
        $context = app(TenantContext::class);

        if ($context->has()) {
            $builder->where($model->qualifyColumn('tenant_id'), $context->get());
        }
    }
}
