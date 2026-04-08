<?php

namespace App\Contexts;

/**
 * Singleton that holds the current tenant_id for the request lifecycle.
 *
 * Set by ResolveTenant middleware, consumed by TenantScope and BelongsToTenant.
 */
class TenantContext
{
    private ?int $tenantId = null;

    public function set(int $tenantId): void
    {
        $this->tenantId = $tenantId;
    }

    public function get(): ?int
    {
        return $this->tenantId;
    }

    public function require(): int
    {
        if (is_null($this->tenantId)) {
            throw new \App\Exceptions\TenantNotFoundException(
                'No tenant has been set for the current request.'
            );
        }

        return $this->tenantId;
    }

    public function has(): bool
    {
        return !is_null($this->tenantId);
    }
}
