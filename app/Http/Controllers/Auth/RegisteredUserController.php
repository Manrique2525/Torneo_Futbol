<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Enums\RoleEnum;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Tenant;
use App\Models\User;
use App\Services\RolePermissionService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    public function __construct(
        private readonly RolePermissionService $roleService
    ) {}

    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * Flow:
     * 1. Validate input
     * 2. Create Tenant (the organization)
     * 3. Create User with tenant_id
     * 4. Setup default roles & permissions for the tenant
     * 5. Assign 'admin' role to the user
     * 6. Login and redirect
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name'              => ['required', 'string', 'max:150'],
            'email'             => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password'          => ['required', 'confirmed', Rules\Password::defaults()],
            'organization_name' => ['required', 'string', 'max:150'],
        ]);

        [$tenant, $user] = DB::transaction(function () use ($request) {

            // 1. Create tenant
            $tenant = Tenant::create([
                'uuid'   => (string) Str::uuid(),
                'name'   => $request->organization_name,
                'slug'   => Str::slug($request->organization_name) . '-' . Str::random(4),
                'email'  => $request->email,
                'status' => Tenant::STATUS_TRIAL,
                'plan'   => 'basic',
            ]);

            // 2. Create user
            $user = User::withoutGlobalScopes()->create([
                'tenant_id' => $tenant->id,
                'name'      => $request->name,
                'email'     => $request->email,
                'password'  => Hash::make($request->password),
                'status'    => User::STATUS_ACTIVE,
            ]);

            // 3. Setup roles & permissions for this tenant
            $this->roleService->setupTenantRoles($tenant->id);

            // 4. Assign admin role
            setPermissionsTeamId($tenant->id);
            $user->assignRole(RoleEnum::ADMIN->value);


            // 5. Create trial subscription
            $plan = Plan::where('slug', 'basic')->first();

            Subscription::create([
                'tenant_id'      => $tenant->id,
                'plan_id'        => $plan->id,
                'status'         => Subscription::STATUS_TRIAL,
                'billing_cycle'  => Subscription::BILLING_MONTHLY,
                'price_paid'     => 0,
                'discount_amount' => 0,
                'starts_at'      => now(),
                'trial_ends_at'  => now()->addDays(14),
                'ends_at'        => now()->addDays(14),
                'auto_renew'     => true,
            ]);

            return [$tenant, $user];
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
