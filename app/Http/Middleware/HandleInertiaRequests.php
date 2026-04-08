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
        $authData = null;

        if ($user) {
            try {
                if ($user->tenant_id) {
                    setPermissionsTeamId($user->tenant_id);
                }

                $authData = [
                    'id'          => $user->id,
                    'name'        => $user->name,
                    'email'       => $user->email,
                    'tenant_id'   => $user->tenant_id,
                    'roles'       => $user->tenant_id ? $user->getRoleNames() : [],
                    'permissions' => $user->tenant_id ? $user->getAllPermissions()->pluck('name') : [],
                ];
            } catch (\Throwable $e) {
                // THIS will show you the real error
                logger()->error('HandleInertiaRequests auth error: ' . $e->getMessage());

                $authData = [
                    'id'          => $user->id,
                    'name'        => $user->name,
                    'email'       => $user->email,
                    'tenant_id'   => $user->tenant_id,
                    'roles'       => [],
                    'permissions' => [],
                ];
            }
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $authData,
            ],
            'flash' => [
                'success' => fn() => $request->session()->get('success'),
            ],
        ];
    }
}
