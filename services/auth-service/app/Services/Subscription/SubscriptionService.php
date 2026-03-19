<?php

namespace App\Services\Subscription;

use App\Models\Plan;
use App\Models\Subscription;
use Shared\Core\Services\BaseService;
use Illuminate\Support\Facades\DB;
use Shared\Core\Events\AuditTrail;
use Shared\Core\Outbox\OutboxPublisher;
use Shared\Core\MultiTenancy\TenantManager;

class SubscriptionService extends BaseService
{
    public function __construct(AuditTrail $auditTrail, OutboxPublisher $outbox, TenantManager $tenantManager)
    {
        parent::__construct($auditTrail, $outbox, $tenantManager);
    }

    /**
     * Upgrades or creates a subscription for a tenant.
     */
    public function upgrade(string $tenantId, int $planId): Subscription
    {
        return $this->transactionalOperation(function() use ($tenantId, $planId) {
            $plan = Plan::findOrFail($planId);

            // Update or Create Subscription
            $subscription = Subscription::updateOrCreate(
                ['tenant_id' => $tenantId],
                [
                    'plan_id' => $planId,
                    'start_date' => now(),
                    'end_date' => $plan->billing_cycle === 'monthly' ? now()->addMonth() : now()->addYear(),
                    'status' => 'active',
                ]
            );

            return $subscription;
        }, 'subscription_upgraded', ['tenant_id' => $tenantId, 'plan_id' => $planId]);
    }

    /**
     * Cancels an active subscription.
     */
    public function cancel(string $tenantId): bool
    {
        return $this->transactionalOperation(function() use ($tenantId) {
            $subscription = Subscription::where('tenant_id', $tenantId)->first();

            if ($subscription && $subscription->isActive()) {
                return $subscription->update(['status' => 'cancelled', 'end_date' => now()]);
            }

            return false;
        }, 'subscription_cancelled', ['tenant_id' => $tenantId]);
    }
}
