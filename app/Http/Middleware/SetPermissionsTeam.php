<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
/**
 * Sets the active tenant_id for Spatie's teams feature.
 *
 * This MUST run BEFORE SubstituteBindings in the middleware priority.
 * It tells Spatie which tenant to scope roles/permissions to.
 */
class SetPermissionsTeam
{
    public function handle(Request $request, Closure $next): Response
    {

        /** @var User $user */
        $user = Auth::user();

        if ($user && $user->tenant_id) {
            // This is Spatie's global function — sets the "team" context
            setPermissionsTeamId($user->tenant_id);
        }

        return $next($request);
    }
}
