<?php

namespace App\Application\Services;

use App\Application\Saga\SagaOrchestrator;
use App\Application\Saga\Steps\ReserveInventoryStep;
use App\Application\Saga\Steps\ProcessPaymentStep;
use App\Application\Saga\Steps\CreateOrderStep;
use App\Application\Saga\Steps\SendNotificationStep;
use App\Domain\Order\Entities\Order;
use App\Domain\Order\Repositories\OrderRepositoryInterface;
use App\Domain\Saga\Entities\SagaStateRecord;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

class OrderService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository
    ) {}

    // -------------------------------------------------------------------------
    // Create – Saga orchestration
    // -------------------------------------------------------------------------

    /**
     * Run the full order creation saga.
     *
     * @throws \RuntimeException when the saga fails and no order was persisted
     */
    public function createOrder(array $data): array
    {
        // Pre-generate order number so it can be referenced in all saga steps
        $data['order_number'] = Order::generateOrderNumber();

        $orchestrator = $this->buildOrchestrator();

        $sagaState = $orchestrator->execute($data);

        if ($sagaState->isFailed() || $sagaState->isCompensated()) {
            throw new \RuntimeException(
                'Order creation failed: ' . ($sagaState->getFailureReason() ?? 'Unknown error')
            );
        }

        $context = $sagaState->getContext();
        $order   = $this->orderRepository->findById($context['order_id']);

        return [
            'order'   => $order,
            'saga_id' => $sagaState->getSagaId(),
            'status'  => $sagaState->getStatus(),
        ];
    }

    // -------------------------------------------------------------------------
    // Read
    // -------------------------------------------------------------------------

    public function getOrder(string|int $id, string $tenantId): Order
    {
        $order = $this->orderRepository->findById($id);

        if (!$order || $order->tenant_id !== $tenantId) {
            throw new \RuntimeException('Order not found', 404);
        }

        return $order;
    }

    public function listOrders(string $tenantId, int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->orderRepository->getAllForTenant($tenantId, $perPage, $filters);
    }

    public function getOrderStats(string $tenantId): array
    {
        return $this->orderRepository->getOrderStatistics($tenantId);
    }

    // -------------------------------------------------------------------------
    // Cancel
    // -------------------------------------------------------------------------

    public function cancelOrder(string|int $id, string $tenantId, string $reason = ''): Order
    {
        $order = $this->getOrder($id, $tenantId);

        if (!$order->isCancellable()) {
            throw new \RuntimeException("Order [{$order->order_number}] cannot be cancelled in status [{$order->status}]");
        }

        $this->orderRepository->update($id, [
            'status'   => Order::STATUS_CANCELLED,
            'metadata' => array_merge($order->metadata ?? [], [
                'cancellation_reason' => $reason,
                'cancelled_at'        => now()->toIso8601String(),
            ]),
        ]);

        Log::info("Order [{$order->order_number}] cancelled", ['tenant_id' => $tenantId, 'reason' => $reason]);

        return $order->fresh();
    }

    // -------------------------------------------------------------------------
    // Saga status lookup
    // -------------------------------------------------------------------------

    public function getSagaStatus(string $sagaId, string $tenantId): array
    {
        $record = SagaStateRecord::where('saga_id', $sagaId)->firstOrFail();

        // Ensure the saga belongs to the requesting tenant via the order
        $order = $this->orderRepository->findBySagaId($sagaId);
        if ($order && $order->tenant_id !== $tenantId) {
            throw new \RuntimeException('Saga not found for this tenant', 404);
        }

        return [
            'saga_id'           => $record->saga_id,
            'status'            => $record->status,
            'completed_steps'   => $record->completed_steps,
            'compensated_steps' => $record->compensated_steps,
            'failure_reason'    => $record->failure_reason,
            'events'            => $record->events,
            'order_id'          => $order?->id,
            'order_number'      => $order?->order_number,
        ];
    }

    // -------------------------------------------------------------------------
    // Orchestrator factory
    // -------------------------------------------------------------------------

    private function buildOrchestrator(): SagaOrchestrator
    {
        return (new SagaOrchestrator())
            ->addStep(new ReserveInventoryStep(config('services.inventory_service_url')))
            ->addStep(new ProcessPaymentStep(config('services.payment_gateway_url')))
            ->addStep(new CreateOrderStep())
            ->addStep(new SendNotificationStep(config('services.notification_service_url')));
    }
}
