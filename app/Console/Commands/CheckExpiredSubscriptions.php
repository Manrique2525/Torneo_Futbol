<?php
namespace App\Console\Commands;

use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckExpiredSubscriptions extends Command
{
    protected $signature = 'subscriptions:check-expired';

    protected $description = 'Suspend tenants with expired trial or unpaid subscriptions';

    public function handle(): int
    {
        $this->info('Checking expired subscriptions...');

        $suspended = 0;
        $pastDue = 0;

        // ── 1. Expired trials ───────────────────────────
        // Subscriptions in trial where trial_ends_at has passed
        $expiredTrials = Subscription::where('status', Subscription::STATUS_TRIAL)
            ->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '<', now())
            ->with('tenant')
            ->get();

        foreach ($expiredTrials as $subscription) {
            DB::transaction(function () use ($subscription) {
                $subscription->update([
                    'status' => Subscription::STATUS_SUSPENDED,
                ]);

                $subscription->tenant->update([
                    'status' => Tenant::STATUS_SUSPENDED,
                ]);
            });

            $suspended++;

            Log::info('Tenant suspended (trial expired)', [
                'tenant_id'   => $subscription->tenant_id,
                'tenant_name' => $subscription->tenant->name,
                'trial_ended' => $subscription->trial_ends_at->toDateString(),
            ]);
        }

        // ── 2. Expired active subscriptions ─────────────
        // Active subscriptions where ends_at has passed (didn't renew/pay)
        $expiredActive = Subscription::where('status', Subscription::STATUS_ACTIVE)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now())
            ->with('tenant')
            ->get();

        foreach ($expiredActive as $subscription) {
            DB::transaction(function () use ($subscription) {
                $subscription->update([
                    'status' => Subscription::STATUS_PAST_DUE,
                ]);

                // Give 3 days grace period before suspending
                // Don't suspend immediately — mark as past_due first
            });

            $pastDue++;

            Log::info('Subscription marked past_due', [
                'tenant_id'   => $subscription->tenant_id,
                'tenant_name' => $subscription->tenant->name,
                'ended_at'    => $subscription->ends_at->toDateString(),
            ]);
        }

        // ── 3. Past due for more than 3 days → suspend ─
        // Grace period expired, now suspend
        $gracePeriodExpired = Subscription::where('status', Subscription::STATUS_PAST_DUE)
            ->whereNotNull('ends_at')
            ->where('ends_at', '<', now()->subDays(3))
            ->with('tenant')
            ->get();

        foreach ($gracePeriodExpired as $subscription) {
            DB::transaction(function () use ($subscription) {
                $subscription->update([
                    'status' => Subscription::STATUS_SUSPENDED,
                ]);

                $subscription->tenant->update([
                    'status' => Tenant::STATUS_SUSPENDED,
                ]);
            });

            $suspended++;

            Log::info('Tenant suspended (grace period expired)', [
                'tenant_id'   => $subscription->tenant_id,
                'tenant_name' => $subscription->tenant->name,
            ]);
        }

        // ── Summary ─────────────────────────────────────
        $this->info("Done. Suspended: {$suspended} | Past due: {$pastDue}");

        if ($suspended > 0 || $pastDue > 0) {
            Log::info('CheckExpiredSubscriptions summary', [
                'suspended' => $suspended,
                'past_due'  => $pastDue,
            ]);
        }

        return self::SUCCESS;
    }
}
