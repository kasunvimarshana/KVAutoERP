<?php

namespace App\Services;

use Shared\Core\Services\BaseService;
use App\Models\Order;
use App\Repositories\OrderRepository;
use Shared\Core\Saga\SagaOrchestrator;
use App\Services\SagaSteps\CreateOrderStep;
use App\Services\SagaSteps\ReserveInventoryStep;
use App\Services\SagaSteps\ProcessPaymentStep;
use App\Services\SagaSteps\UpdateOrderStatusStep;
use App\Services\SagaSteps\SendConfirmationStep;
use Illuminate\Support\Str;

class OrderService extends BaseService
{
    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    public function __construct(OrderRepository $orderRepository, \Shared\Core\Events\AuditTrail $auditTrail, \Shared\Core\Outbox\OutboxPublisher $outbox, \Shared\Core\MultiTenancy\TenantManager $tenantManager)
    {
        parent::__construct($auditTrail, $outbox, $tenantManager);
        $this->orderRepository = $orderRepository;
    }

    /**
     * Create a new order using the Saga pattern with transactional guarantees and outbox pattern.
     */
    public function createOrder(array $data)
    {
        $sagaId = Str::uuid()->toString();
        $orchestrator = new SagaOrchestrator($sagaId);

        // Define Saga steps
        $orchestrator->addStep(new CreateOrderStep($this->orderRepository))
                    ->addStep(new ReserveInventoryStep())
                    ->addStep(new ProcessPaymentStep())
                    ->addStep(new UpdateOrderStatusStep($this->orderRepository))
                    ->addStep(new SendConfirmationStep());

        // Use BaseService transactional wrapper for auditing and outbox delivery
        return $this->transactionalOperation(function() use ($orchestrator, $data) {
            $success = $orchestrator->execute($data);

            if ($success) {
                $results = $orchestrator->getResults();
                return $results[0]['order']; // The created order from step 1
            }

            return false;
        }, 'order_created', $data);
    }
}
