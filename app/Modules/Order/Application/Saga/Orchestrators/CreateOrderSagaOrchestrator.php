<?php

declare(strict_types=1);

namespace App\Modules\Order\Application\Saga\Orchestrators;

use App\Core\Contracts\MessageBroker\MessageBrokerInterface;
use App\Core\Exceptions\SagaException;
use App\Modules\Order\Domain\Models\Order;
use App\Modules\Order\Infrastructure\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * CreateOrderSagaOrchestrator
 *
 * Implements the Saga orchestration pattern for order creation.
 *
 * Saga steps (happy path):
 *   1. CreateOrderStep       – persist order in PENDING state
 *   2. ReserveInventoryStep  – lock inventory stock for each order line
 *   3. ProcessPaymentStep    – charge the customer
 *   4. ConfirmOrderStep      – transition order → CONFIRMED
 *
 * Compensating transactions (rollback on failure):
 *   - If Payment fails  → ReleaseInventoryCompensation, CancelOrderCompensation
 *   - If Inventory fails → CancelOrderCompensation
 *
 * All saga state is persisted to DB so the orchestrator can resume after a crash.
 */
class CreateOrderSagaOrchestrator
{
    private string $correlationId;

    public function __construct(
        private readonly OrderRepository        $orderRepository,
        private readonly MessageBrokerInterface $broker,
        \App\Modules\Order\Application\Saga\Steps\CreateOrderStep            $createOrderStep,
        \App\Modules\Order\Application\Saga\Steps\ReserveInventoryStep       $reserveInventoryStep,
        \App\Modules\Order\Application\Saga\Steps\ProcessPaymentStep         $processPaymentStep,
        \App\Modules\Order\Application\Saga\Steps\ConfirmOrderStep           $confirmOrderStep
    ) {
        $this->correlationId = (string) Str::uuid();
    }

    /**
     * Execute the full Create-Order Saga.
     *
     * @param  array<string,mixed> $orderData   { customer_id, items: [{product_id, quantity}], ... }
     * @return Order
     *
     * @throws SagaException  when the saga cannot be completed
     */
    public function execute(array $orderData): Order
    {
        Log::info("[Saga:{$this->correlationId}] Starting CreateOrder saga.");

        $executedSteps = [];
        $order         = null;

        try {
            // ------------------------------------------------------------------
            //  Step 1 – Create order record
            // ------------------------------------------------------------------
            $order = $this->runStep('CreateOrder', function () use ($orderData): Order {
                return DB::transaction(function () use ($orderData): Order {
                    $order = $this->orderRepository->create([
                        'tenant_id'           => $orderData['tenant_id'],
                        'customer_id'         => $orderData['customer_id'],
                        'status'              => Order::STATUS_PENDING,
                        'saga_status'         => Order::SAGA_PENDING,
                        'saga_correlation_id' => $this->correlationId,
                        'total_amount'        => 0, // calculated after items
                        'metadata'            => $orderData['metadata'] ?? null,
                    ]);

                    // Persist order items and compute total
                    $total = 0.0;
                    foreach ($orderData['items'] as $item) {
                        $order->items()->create([
                            'product_id' => $item['product_id'],
                            'quantity'   => $item['quantity'],
                            'unit_price' => $item['unit_price'],
                            'subtotal'   => $item['quantity'] * $item['unit_price'],
                        ]);
                        $total += $item['quantity'] * $item['unit_price'];
                    }

                    $order->update(['total_amount' => $total]);
                    return $order->refresh();
                });
            });
            $executedSteps[] = 'CreateOrder';

            // ------------------------------------------------------------------
            //  Step 2 – Reserve inventory
            // ------------------------------------------------------------------
            $this->runStep('ReserveInventory', function () use ($order): void {
                DB::transaction(function () use ($order): void {
                    foreach ($order->items as $item) {
                        $product = \App\Modules\Inventory\Domain\Models\Product::findOrFail($item->product_id);
                        $product->reserve($item->quantity);
                    }
                });
            });
            $executedSteps[] = 'ReserveInventory';

            // ------------------------------------------------------------------
            //  Step 3 – Process payment (stub – integrates with payment gateway)
            // ------------------------------------------------------------------
            $this->runStep('ProcessPayment', function () use ($order): void {
                // In a real system, call the Payment microservice via HTTP or message broker
                // Simulate success here; failures will trigger compensation automatically
                Log::info("[Saga:{$this->correlationId}] Payment step executed for order [{$order->id}].");
            });
            $executedSteps[] = 'ProcessPayment';

            // ------------------------------------------------------------------
            //  Step 4 – Confirm order
            // ------------------------------------------------------------------
            $this->runStep('ConfirmOrder', function () use ($order): void {
                DB::transaction(function () use ($order): void {
                    $order->update([
                        'status'      => Order::STATUS_CONFIRMED,
                        'saga_status' => Order::SAGA_COMPLETED,
                    ]);
                });
            });

            Log::info("[Saga:{$this->correlationId}] CreateOrder saga completed for order [{$order->id}].");

            // Publish success event for downstream microservices
            $this->broker->publish('order.created', [
                'correlation_id' => $this->correlationId,
                'order_id'       => $order->id,
                'tenant_id'      => $order->tenant_id,
                'status'         => Order::STATUS_CONFIRMED,
            ]);

            return $order->refresh();

        } catch (\Throwable $e) {
            Log::error("[Saga:{$this->correlationId}] Saga failed at step. Compensating. Error: {$e->getMessage()}");

            $this->compensate($order, $executedSteps, $e);

            throw new SagaException(
                "CreateOrder Saga failed: {$e->getMessage()}",
                0,
                $e
            );
        }
    }

    // -------------------------------------------------------------------------
    //  Saga step runner
    // -------------------------------------------------------------------------

    /**
     * Execute a single saga step, logging start/end.
     *
     * @template T
     * @param  string   $stepName
     * @param  callable $step      Returns T
     * @return T
     */
    private function runStep(string $stepName, callable $step): mixed
    {
        Log::info("[Saga:{$this->correlationId}] Executing step [{$stepName}].");
        $result = $step();
        Log::info("[Saga:{$this->correlationId}] Step [{$stepName}] succeeded.");
        return $result;
    }

    // -------------------------------------------------------------------------
    //  Compensation (rollback)
    // -------------------------------------------------------------------------

    /**
     * Run compensating transactions for all successfully executed steps
     * in reverse order (LIFO).
     *
     * @param  Order|null          $order
     * @param  array<string>       $executedSteps
     * @param  \Throwable          $originalError
     */
    private function compensate(?Order $order, array $executedSteps, \Throwable $originalError): void
    {
        if ($order !== null) {
            $order->update(['saga_status' => Order::SAGA_COMPENSATING]);
        }

        // Compensate in reverse order
        foreach (array_reverse($executedSteps) as $step) {
            try {
                $this->runCompensation($step, $order);
            } catch (\Throwable $e) {
                // Log compensation failure but continue compensating other steps
                Log::critical("[Saga:{$this->correlationId}] Compensation for [{$step}] FAILED: {$e->getMessage()}");
            }
        }

        if ($order !== null) {
            $order->update([
                'status'      => Order::STATUS_FAILED,
                'saga_status' => Order::SAGA_COMPENSATED,
            ]);
        }

        // Publish failure event
        $this->broker->publish('order.saga.failed', [
            'correlation_id' => $this->correlationId,
            'order_id'       => $order?->id,
            'reason'         => $originalError->getMessage(),
        ]);
    }

    /**
     * Execute the compensating transaction for a given step.
     */
    private function runCompensation(string $step, ?Order $order): void
    {
        Log::info("[Saga:{$this->correlationId}] Compensating step [{$step}].");

        match ($step) {
            'ReserveInventory' => $this->compensateInventoryReservation($order),
            'CreateOrder'      => $this->compensateCancelOrder($order),
            'ProcessPayment'   => $this->compensateRefundPayment($order),
            default            => null,
        };
    }

    private function compensateInventoryReservation(?Order $order): void
    {
        if ($order === null) {
            return;
        }

        DB::transaction(function () use ($order): void {
            foreach ($order->items as $item) {
                $product = \App\Modules\Inventory\Domain\Models\Product::find($item->product_id);
                $product?->releaseReservation($item->quantity);
            }
        });

        Log::info("[Saga:{$this->correlationId}] Inventory reservation released for order [{$order->id}].");
    }

    private function compensateCancelOrder(?Order $order): void
    {
        if ($order === null) {
            return;
        }

        $order->update(['status' => Order::STATUS_CANCELLED]);
        Log::info("[Saga:{$this->correlationId}] Order [{$order->id}] cancelled via compensation.");
    }

    private function compensateRefundPayment(?Order $order): void
    {
        if ($order === null) {
            return;
        }

        // In a real system, call the Payment microservice to issue a refund
        Log::info("[Saga:{$this->correlationId}] Payment refund triggered for order [{$order->id}].");
    }
}
