<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    /**
     * Ensure only super_admin can access.
     */
    public function __construct()
    {
    }

    /**
     * List all plans.
     */
    public function index(): Response
    {
            if (!auth()->user()?->isSuperAdmin()) {
        abort(403, 'Only SaaS administrators can manage plans.');
    }
        $plans = Plan::ordered()->get()->map(function ($plan) {
            return [
                'id'                => $plan->id,
                'name'              => $plan->name,
                'slug'              => $plan->slug,
                'description'       => $plan->description,
                'monthly_price'     => $plan->monthly_price,
                'annual_price'      => $plan->annual_price,
                'currency'          => $plan->currency,
                'max_tournaments'   => $plan->max_tournaments,
                'max_teams'         => $plan->max_teams,
                'max_players'       => $plan->max_players,
                'max_users'         => $plan->max_users,
                'max_fields'        => $plan->max_fields,
                'max_referees'      => $plan->max_referees,
                'storage_mb'        => $plan->storage_mb,
                'has_mobile_app'    => $plan->has_mobile_app,
                'has_streaming'     => $plan->has_streaming,
                'has_advanced_stats' => $plan->has_advanced_stats,
                'has_api_access'    => $plan->has_api_access,
                'has_whatsapp'      => $plan->has_whatsapp,
                'has_reports'       => $plan->has_reports,
                'has_custom_domain' => $plan->has_custom_domain,
                'support_level'     => $plan->support_level,
                'sort_order'        => $plan->sort_order,
                'is_active'         => $plan->is_active,
                'is_featured'       => $plan->is_featured,
                'tenants_count'     => $plan->tenantsCount(),
            ];
        });

        return Inertia::render('Plans/Index', [
            'plans' => $plans,
        ]);
    }

    /**
     * Store a new plan.
     */
    public function store(Request $request): RedirectResponse
    {
                   if (!auth()->user()?->isSuperAdmin()) {
        abort(403, 'Only SaaS administrators can manage plans.');
    }
        $validated = $request->validate($this->rules());

        Plan::create($validated);

        return back()->with('success', 'Plan created successfully.');
    }

    /**
     * Update a plan.
     */
    public function update(Request $request, Plan $plan): RedirectResponse
    {
                   if (!auth()->user()?->isSuperAdmin()) {
        abort(403, 'Only SaaS administrators can manage plans.');
    }
        $rules = $this->rules($plan->id);
        $validated = $request->validate($rules);

        $plan->update($validated);

        return back()->with('success', 'Plan updated successfully.');
    }

    /**
     * Delete a plan (only if no tenants use it).
     */
    public function destroy(Plan $plan): RedirectResponse
    {
                   if (!auth()->user()?->isSuperAdmin()) {
        abort(403, 'Only SaaS administrators can manage plans.');
    }
        if ($plan->tenantsCount() > 0) {
            return back()->with('error', "Cannot delete plan '{$plan->name}' — {$plan->tenantsCount()} organizations are using it.");
        }

        $plan->delete();

        return back()->with('success', 'Plan deleted successfully.');
    }

    /**
     * Validation rules.
     */
    private function rules(?int $ignoreId = null): array
    {

               if (!auth()->user()?->isSuperAdmin()) {
        abort(403, 'Only SaaS administrators can manage plans.');
    }
        return [
            'name'              => ['required', 'string', 'max:80'],
            'slug'              => ['required', 'string', 'max:80', 'regex:/^[a-z0-9_-]+$/', Rule::unique('plans')->ignore($ignoreId)],
            'description'       => ['nullable', 'string', 'max:500'],
            'monthly_price'     => ['required', 'numeric', 'min:0'],
            'annual_price'      => ['required', 'numeric', 'min:0'],
            'currency'          => ['required', 'string', 'size:3'],
            'max_tournaments'   => ['required', 'integer', 'min:-1'],
            'max_teams'         => ['required', 'integer', 'min:-1'],
            'max_players'       => ['required', 'integer', 'min:-1'],
            'max_users'         => ['required', 'integer', 'min:-1'],
            'max_fields'        => ['required', 'integer', 'min:-1'],
            'max_referees'      => ['required', 'integer', 'min:-1'],
            'storage_mb'        => ['required', 'integer', 'min:0'],
            'has_mobile_app'    => ['boolean'],
            'has_streaming'     => ['boolean'],
            'has_advanced_stats' => ['boolean'],
            'has_api_access'    => ['boolean'],
            'has_whatsapp'      => ['boolean'],
            'has_reports'       => ['boolean'],
            'has_custom_domain' => ['boolean'],
            'support_level'     => ['required', Rule::in(['basic', 'priority', 'dedicated'])],
            'sort_order'        => ['required', 'integer', 'min:0'],
            'is_active'         => ['boolean'],
            'is_featured'       => ['boolean'],
        ];
    }
}
