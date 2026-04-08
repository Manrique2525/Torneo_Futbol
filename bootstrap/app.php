<?php

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\ResolveTenant;
use App\Http\Middleware\SetPermissionsTeam;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\SubstituteBindings;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__ . '/../routes/api.php',
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        $middleware->web(append: [
            HandleInertiaRequests::class,  // ← ESTO FALTABA
            SetPermissionsTeam::class,
        ]);

        $middleware->priority([
            SetPermissionsTeam::class,
            SubstituteBindings::class,
        ]);

        $middleware->alias([
            'tenant' => ResolveTenant::class,
        ]);
    })
    ->withExceptions(function ($exceptions) {
        //
    })
    ->create();
