<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */


public function share(Request $request): array
{
    $user = $request->user();

    if (!$user) {
        return parent::share($request);
    }

    // 🔥 FORZAR CONTEXTO SPATIE AQUÍ
    setPermissionsTeamId($user->tenant_id);

    return [
        ...parent::share($request),

        'auth' => [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'tenant_id' => $user->tenant_id,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name')->values(),
            ],
        ],

        'flash' => [
            'success' => fn () => $request->session()->get('success'),
        ],
    ];
}
}
