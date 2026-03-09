<?php

declare(strict_types=1);

namespace App\Domain\Saga\Steps;

use App\Domain\Saga\Context\SagaContext;
use App\Domain\Saga\Step\AbstractSagaStep;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Process Payment Step.
 *
 * Saga Step 2: Charge the customer via the Payment Service.
 *
 * Execute:    POST /api/payments/charge
 * Compensate: POST /api/payments/{payment_id}/refund
 */
class ProcessPaymentStep extends AbstractSagaStep
{
    public function __construct(
        private readonly string $paymentServiceUrl,
        private readonly string $serviceToken,
    ) {}

    public function getName(): string
    {
        return 'process_payment';
    }

    /**
     * Charge the customer for the order amount.
     *
     * @param  SagaContext $context
     * @return void
     * @throws \RuntimeException On payment failure
     */
    public function execute(SagaContext $context): void
    {
        $orderId      = $context->get('order_id');
        $tenantId     = $context->get('tenant_id');
        $customerId   = $context->get('customer_id');
        $totalAmount  = $context->get('total_amount');
        $currency     = $context->get('currency', 'USD');
        $paymentMethod = $context->get('payment_method');

        $response = Http::timeout(60)
            ->withToken($this->serviceToken)
            ->withHeaders(['X-Tenant-ID' => $tenantId])
            ->post("{$this->paymentServiceUrl}/api/payments/charge", [
                'order_id'       => $orderId,
                'customer_id'    => $customerId,
                'amount'         => $totalAmount,
                'currency'       => $currency,
                'payment_method' => $paymentMethod,
                'metadata'       => ['source' => 'ims-order-service'],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException(
                "Payment failed for order [{$orderId}]: " .
                $response->json('message', 'Unknown payment error'),
            );
        }

        $paymentData = $response->json('data');

        // Store payment ID in context for compensation (refund)
        $context->set('payment_id', $paymentData['payment_id']);
        $context->set('payment_status', $paymentData['status']);

        Log::info('Payment processed', [
            'order_id'   => $orderId,
            'payment_id' => $paymentData['payment_id'],
            'amount'     => $totalAmount,
        ]);
    }

    /**
     * Refund the payment (compensating transaction).
     *
     * @param  SagaContext $context
     * @return void
     */
    public function compensate(SagaContext $context): void
    {
        $paymentId = $context->get('payment_id');
        $tenantId  = $context->get('tenant_id');
        $orderId   = $context->get('order_id');

        if ($paymentId === null) {
            // Payment was never created - nothing to compensate
            return;
        }

        try {
            Http::timeout(60)
                ->withToken($this->serviceToken)
                ->withHeaders(['X-Tenant-ID' => $tenantId])
                ->post("{$this->paymentServiceUrl}/api/payments/{$paymentId}/refund", [
                    'reason'   => "Order {$orderId} saga compensation",
                    'order_id' => $orderId,
                ]);

            Log::info('Payment refunded (compensation)', [
                'payment_id' => $paymentId,
                'order_id'   => $orderId,
            ]);
        } catch (\Throwable $e) {
            Log::critical('Payment refund failed - manual intervention required', [
                'payment_id' => $paymentId,
                'order_id'   => $orderId,
                'error'      => $e->getMessage(),
            ]);

            throw $e; // Re-throw to signal compensation failure
        }
    }
}
