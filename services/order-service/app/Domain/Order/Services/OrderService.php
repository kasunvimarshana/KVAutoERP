<?php

declare(strict_types=1);

namespace App\Domain\Order\Services;

use App\Domain\Order\Entities\Order;
use App\Domain\Order\Entities\OrderItem;
use App\Domain\Order\Repositories\Interfaces\OrderRepositoryInterface;
use App\Domain\Saga\Orchestrator\SagaOrchestrator;
use App\Domain\Saga\Result\SagaResult;
use App\Domain\Saga\Steps\ConfirmOrderStep;
use App\Domain\Saga\Steps\ProcessPaymentStep;
use App\Domain\Saga\Steps\ReserveInventoryStep;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Order Service.
 *
 * Orchestrates order lifecycle and initiates Saga for distributed transactions.
 */
class OrderService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
    ) {}

    // =========================================================================
    // Order CRUD
    // =========================================================================

    /**
     * List orders with filtering, sorting, and conditional pagination.
     *
     * @param  string                                   $tenantId
     * @param  array<string, mixed>                     $params
     * @return LengthAwarePaginator|Collection<int, Order>
     */
    public function list(string $tenantId, array $params = []): LengthAwarePaginator|Collection
    {
        $params['filters']['tenant_id'] = $tenantId;

        return $this->orderRepository->all($params);
    }

    /**
     * Get a single order by ID.
     *
     * @param  string $id
     * @param  string $tenantId
     * @return Order
     */
    public function getById(string $id, string $tenantId): Order
    {
        $order = $this->orderRepository->find($id);

        if ($order === null || $order->tenant_id !== $tenantId) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException("Order [{$id}] not found.");
        }

        return $order;
    }

    /**
     * Create a new order and initiate the Saga for distributed processing.
     *
     * @param  string               $tenantId
     * @param  array<string, mixed> $data
     * @return array{order: Order, saga_result: SagaResult}
     */
    public function createAndProcess(string $tenantId, array $data): array
    {
        // Step 1: Persist the order in pending state (local ACID transaction)
        $order = DB::transaction(function () use ($tenantId, $data): Order {
            $order = $this->orderRepository->create([
                'tenant_id'    => $tenantId,
                'customer_id'  => $data['customer_id'],
                'status'       => 'pending',
                'saga_status'  => 'pending',
                'currency'     => $data['currency'] ?? 'USD',
                'notes'        => $data['notes'] ?? null,
                'subtotal'     => $this->calculateSubtotal($data['items']),
                'tax_amount'   => $data['tax_amount'] ?? 0,
                'total_amount' => $this->calculateTotal($data),
            ]);

            // Create order items
            foreach ($data['items'] as $item) {
                OrderItem::create([
                    'order_id'          => $order->id,
                    'inventory_item_id' => $item['inventory_item_id'],
                    'sku'               => $item['sku'],
                    'name'              => $item['name'],
                    'quantity'          => $item['quantity'],
                    'unit_price'        => $item['unit_price'],
                    'total_price'       => $item['quantity'] * $item['unit_price'],
                ]);
            }

            return $order->load('items');
        });

        // Step 2: Execute the distributed Saga
        $sagaResult = $this->executePlaceOrderSaga($order, $data);

        // Step 3: Update order saga status based on result
        $finalStatus = $sagaResult->isSuccess() ? 'completed' : 'failed';
        $order->update([
            'saga_status'         => $finalStatus,
            'saga_transaction_id' => $sagaResult->getTransactionId(),
            'status'              => $sagaResult->isSuccess() ? 'confirmed' : 'failed',
        ]);

        return [
            'order'       => $order->fresh()->load('items'),
            'saga_result' => $sagaResult,
        ];
    }

    /**
     * Cancel an order and execute compensating transactions.
     *
     * @param  string $id
     * @param  string $tenantId
     * @param  string $reason
     * @return Order
     */
    public function cancel(string $id, string $tenantId, string $reason = ''): Order
    {
        $order = $this->getById($id, $tenantId);

        if (!$order->canBeCancelled()) {
            throw new \DomainException("Order [{$id}] cannot be cancelled in state [{$order->status}].");
        }

        return DB::transaction(function () use ($order, $reason): Order {
            $order->update([
                'status'       => 'cancelled',
                'cancelled_at' => now(),
                'metadata'     => array_merge($order->metadata ?? [], [
                    'cancel_reason' => $reason,
                    'cancelled_at'  => now()->toISOString(),
                ]),
            ]);

            return $order->fresh();
        });
    }

    // =========================================================================
    // Saga Orchestration
    // =========================================================================

    /**
     * Execute the place-order saga across services.
     *
     * Steps:
     *   1. Reserve inventory (Inventory Service)
     *   2. Process payment  (Payment Service)
     *   3. Confirm order    (Local)
     *
     * On failure: compensating transactions run in reverse order.
     *
     * @param  Order                $order
     * @param  array<string, mixed> $data
     * @return SagaResult
     */
    private function executePlaceOrderSaga(Order $order, array $data): SagaResult
    {
        $orchestrator = new SagaOrchestrator();

        $orchestrator
            ->addStep(new ReserveInventoryStep(
                inventoryServiceUrl: config('services.inventory.url'),
                serviceToken: config('services.inventory.token', ''),
            ))
            ->addStep(new ProcessPaymentStep(
                paymentServiceUrl: config('services.payment.url'),
                serviceToken: config('services.payment.token', ''),
            ))
            ->addStep(new ConfirmOrderStep());

        $sagaResult = $orchestrator->execute([
            'order_id'       => $order->id,
            'tenant_id'      => $order->tenant_id,
            'customer_id'    => $order->customer_id,
            'items'          => $data['items'],
            'total_amount'   => (string) $order->total_amount,
            'currency'       => $order->currency,
            'payment_method' => $data['payment_method'] ?? null,
        ]);

        if (!$sagaResult->isSuccess()) {
            Log::error('Place order saga failed', [
                'order_id'          => $order->id,
                'transaction_id'    => $sagaResult->getTransactionId(),
                'failed_steps'      => $sagaResult->getFailedSteps(),
                'compensated_steps' => $sagaResult->getCompensatedSteps(),
                'error'             => $sagaResult->getError()?->getMessage(),
            ]);
        }

        return $sagaResult;
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * @param  array<int, array<string, mixed>> $items
     * @return float
     */
    private function calculateSubtotal(array $items): float
    {
        return array_sum(array_map(
            fn ($item) => $item['quantity'] * $item['unit_price'],
            $items,
        ));
    }

    /**
     * @param  array<string, mixed> $data
     * @return float
     */
    private function calculateTotal(array $data): float
    {
        $subtotal = $this->calculateSubtotal($data['items']);
        $tax      = (float) ($data['tax_amount'] ?? 0);

        return $subtotal + $tax;
    }
}
