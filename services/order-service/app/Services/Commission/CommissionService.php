<?php

namespace App\Services\Commission;

use App\Models\Order;
use Shared\Core\Rules\RuleEvaluator;
use Shared\Core\Services\BaseService;
use Shared\Core\Events\AuditTrail;
use Shared\Core\Outbox\OutboxPublisher;
use Shared\Core\MultiTenancy\TenantManager;
use Illuminate\Support\Facades\Log;

class CommissionService extends BaseService
{
    protected $ruleEvaluator;

    public function __construct(
        RuleEvaluator $ruleEvaluator,
        AuditTrail $auditTrail,
        OutboxPublisher $outbox,
        TenantManager $tenantManager
    ) {
        parent::__construct($auditTrail, $outbox, $tenantManager);
        $this->ruleEvaluator = $ruleEvaluator;
    }

    /**
     * Calculates and records sales commissions for an order.
     */
    public function calculateForOrder(Order $order): array
    {
        return $this->transactionalOperation(function() use ($order) {
            $commissions = [];
            $rules = $this->getCommissionRules($order->tenant_id);

            foreach ($rules as $rule) {
                if ($this->ruleEvaluator->evaluate($rule['expression'], ['order' => $order])) {
                    $commissionAmount = $this->applyCommission($order->total_amount, $rule);
                    $commissions[] = [
                        'agent_id' => $rule['agent_id'],
                        'amount' => $commissionAmount,
                        'reason' => $rule['name']
                    ];
                    
                    Log::info("Commission calculated: Agent {$rule['agent_id']}, Amount: {$commissionAmount}");
                }
            }

            return $commissions;
        }, 'commission_calculated', ['order_id' => $order->id]);
    }

    protected function applyCommission(float $totalAmount, array $rule): float
    {
        if ($rule['type'] === 'percentage') {
            return $totalAmount * ($rule['value'] / 100);
        }
        return (float) $rule['value'];
    }

    protected function getCommissionRules(string $tenantId): array
    {
        // Mocking rules from metadata
        return [
            ['agent_id' => 1, 'name' => 'Standard Sales Commission', 'type' => 'percentage', 'value' => 5, 'expression' => '$order.total_amount > 1000'],
            ['agent_id' => 2, 'name' => 'Bonus Fixed Commission', 'type' => 'flat', 'value' => 50, 'expression' => '$order.total_amount > 5000'],
        ];
    }
}
