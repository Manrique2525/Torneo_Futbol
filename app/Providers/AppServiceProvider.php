<?php

namespace App\Providers;

use App\Contexts\TenantContext;
use App\Enums\RoleEnum;
use App\Models\Partido;
use App\Models\TorneoEquipo;
use App\Policies\PartidoPolicy;
use App\Policies\TorneoEquipoPolicy;
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
        Gate::policy(TorneoEquipo::class, TorneoEquipoPolicy::class);
        Gate::policy(Partido::class, PartidoPolicy::class);

        // Super admin bypasses ALL permission checks
/*         Gate::before(function ($user, $ability) {
            if ($user->hasRole(RoleEnum::SUPER_ADMIN->value)) {
                return true;
            }
        });
 */
        Gate::before(function ($user, $ability) {
            return $user->roles()
                ->where('name', RoleEnum::SUPER_ADMIN)
                ->exists()
                ? true
                : null;
        });
    }
}
