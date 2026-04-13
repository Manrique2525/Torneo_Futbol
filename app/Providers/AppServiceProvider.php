<?php

namespace App\Providers;

use App\Contexts\TenantContext;
use App\Enums\RoleEnum;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // TenantContext as singleton — same instance for entire request
        $this->app->singleton(TenantContext::class);
    }

    public function boot(): void
    {
        // Super admin bypasses ALL permission checks
        Gate::before(function ($user, $ability) {
            if ($user->hasRole(RoleEnum::SUPER_ADMIN->value)) {
                return true;
            }
        });
    }
}
