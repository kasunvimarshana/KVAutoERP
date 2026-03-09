<?php

declare(strict_types=1);

namespace App\Domain\Saga\Steps;

use App\Domain\Order\Entities\Order;
use App\Domain\Saga\Context\SagaContext;
use App\Domain\Saga\Step\AbstractSagaStep;
use Illuminate\Support\Facades\Log;

/**
 * Confirm Order Step.
 *
 * Saga Step 3: Mark the order as confirmed after successful payment and inventory reservation.
 *
 * Execute:    Update order status to 'confirmed'
 * Compensate: Revert order status to 'cancelled'
 */
class ConfirmOrderStep extends AbstractSagaStep
{
    public function getName(): string
    {
        return 'confirm_order';
    }

    /**
     * Confirm the order.
     *
     * @param  SagaContext $context
     * @return void
     */
    public function execute(SagaContext $context): void
    {
        $orderId = $context->get('order_id');

        /** @var Order $order */
        $order = Order::findOrFail($orderId);

        $order->update([
            'status'               => 'confirmed',
            'saga_status'          => 'running',
            'saga_transaction_id'  => $context->getTransactionId(),
            'metadata'             => array_merge($order->metadata ?? [], [
                'payment_id'     => $context->get('payment_id'),
                'confirmed_at'   => now()->toISOString(),
            ]),
        ]);

        $context->set('order_status', 'confirmed');

        Log::info('Order confirmed', ['order_id' => $orderId]);
    }

    /**
     * Cancel the order (compensating transaction).
     *
     * @param  SagaContext $context
     * @return void
     */
    public function compensate(SagaContext $context): void
    {
        $orderId = $context->get('order_id');

        /** @var Order|null $order */
        $order = Order::find($orderId);

        if ($order === null) {
            return;
        }

        $order->update([
            'status'       => 'cancelled',
            'saga_status'  => 'failed',
            'cancelled_at' => now(),
            'metadata'     => array_merge($order->metadata ?? [], [
                'cancelled_at'    => now()->toISOString(),
                'cancel_reason'   => 'Saga compensation - distributed transaction failed',
            ]),
        ]);

        Log::info('Order cancelled (compensation)', ['order_id' => $orderId]);
    }
}
