<?php

namespace App\Http\Middleware;

use App\Contexts\TenantContext;
use App\Exceptions\TenantNotFoundException;
use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the current tenant from the authenticated user.
 *
 * This middleware:
 * 1. Gets tenant_id from the authenticated user
 * 2. Validates the tenant exists and is active
 * 3. Sets TenantContext for the entire request lifecycle
 * 4. Sets Spatie's team context (for roles/permissions isolation)
 *
 * Apply to all routes that require tenant context:
 *   Route::middleware(['auth:sanctum', 'tenant'])->group(...)
 */
class ResolveTenant
{
    public function __construct(
        private readonly TenantContext $context
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->tenant_id) {
            throw new TenantNotFoundException('User has no tenant assigned.');
        }

        // Validate tenant exists and is active
        $tenant = Tenant::find($user->tenant_id);

        if (!$tenant) {
            throw new TenantNotFoundException('Tenant not found.');
        }

        if ($tenant->status === 'suspended') {
            abort(403, 'Your organization has been suspended. Contact support.');
        }

        // Set tenant context for the entire request
        $this->context->set($tenant->id);

        // Set Spatie's team context (for roles/permissions per tenant)
        setPermissionsTeamId($tenant->id);

        // Share tenant with the request for easy access
        $request->merge(['tenant' => $tenant]);

        return $next($request);
    }
}
