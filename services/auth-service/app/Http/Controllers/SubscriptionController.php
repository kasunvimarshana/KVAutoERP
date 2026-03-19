<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Services\Subscription\SubscriptionService;
use Shared\Core\Http\Controllers\BaseController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends BaseController
{
    protected $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display a listing of subscription plans.
     */
    public function plans(): JsonResponse
    {
        $plans = Plan::where('status', 'active')->get();
        return $this->success($plans);
    }

    /**
     * Upgrade or create a subscription for the current tenant.
     */
    public function upgrade(Request $request): JsonResponse
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $tenantId = $request->header('X-Tenant-ID');
        $subscription = $this->subscriptionService->upgrade($tenantId, $request->plan_id);

        return $this->success($subscription, 'Subscription upgraded successfully.');
    }

    /**
     * Cancel the current subscription.
     */
    public function cancel(Request $request): JsonResponse
    {
        $tenantId = $request->header('X-Tenant-ID');
        $cancelled = $this->subscriptionService->cancel($tenantId);

        if ($cancelled) {
            return $this->success(null, 'Subscription cancelled successfully.');
        }

        return $this->error('No active subscription found to cancel.');
    }
}
