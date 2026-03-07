<?php

namespace App\Application\Saga\Steps;

use App\Application\Saga\SagaState;
use App\Application\Saga\SagaStep;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProcessPaymentStep extends SagaStep
{
    public function __construct(private readonly string $paymentGatewayUrl) {}

    public function getName(): string
    {
        return 'process_payment';
    }

    public function execute(SagaState $state): SagaState
    {
        $payload = $state->getPayload();

        $response = Http::withHeaders([
            'X-Tenant-ID'   => $payload['tenant_id'],
            'Authorization' => 'Bearer ' . ($payload['auth_token'] ?? ''),
            'Accept'        => 'application/json',
        ])->timeout(30)->post("{$this->paymentGatewayUrl}/api/v1/payments/charge", [
            'amount'         => $payload['total'],
            'currency'       => $payload['currency'] ?? 'USD',
            'payment_method' => $payload['payment_method'],
            'customer_id'    => $payload['customer_id'],
            'customer_email' => $payload['customer_email'],
            'order_number'   => $payload['order_number'] ?? null,
            'saga_id'        => $state->getSagaId(),
            'metadata'       => [
                'tenant_id'  => $payload['tenant_id'],
                'items'      => $payload['items'],
            ],
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException(
                "Payment processing failed: " . $response->body()
            );
        }

        $paymentData = $response->json();

        Log::info("Saga [{$state->getSagaId()}]: Payment processed", [
            'payment_id'     => $paymentData['payment_id'] ?? null,
            'transaction_id' => $paymentData['transaction_id'] ?? null,
        ]);

        $state = $this->setContextValue($state, 'payment_id', $paymentData['payment_id'] ?? null);
        $state = $this->setContextValue($state, 'transaction_id', $paymentData['transaction_id'] ?? null);
        $state = $this->setContextValue($state, 'payment_status', $paymentData['status'] ?? 'paid');

        return $state;
    }

    public function compensate(SagaState $state): SagaState
    {
        $context = $state->getContext();
        $payload = $state->getPayload();
        $paymentId = $context['payment_id'] ?? null;

        if (!$paymentId) {
            Log::warning("Saga [{$state->getSagaId()}]: No payment_id in context, skipping refund");
            return $state;
        }

        try {
            $response = Http::withHeaders([
                'X-Tenant-ID'   => $payload['tenant_id'],
                'Authorization' => 'Bearer ' . ($payload['auth_token'] ?? ''),
                'Accept'        => 'application/json',
            ])->timeout(30)->post("{$this->paymentGatewayUrl}/api/v1/payments/{$paymentId}/refund", [
                'amount'  => $payload['total'],
                'reason'  => 'Order saga compensation',
                'saga_id' => $state->getSagaId(),
            ]);

            if ($response->successful()) {
                Log::info("Saga [{$state->getSagaId()}]: Refund issued for payment [{$paymentId}]");
                $state = $this->setContextValue($state, 'refund_id', $response->json('refund_id'));
            } else {
                Log::error("Saga [{$state->getSagaId()}]: Refund failed for payment [{$paymentId}]", [
                    'response' => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error("Saga [{$state->getSagaId()}]: Exception during refund for payment [{$paymentId}]", [
                'error' => $e->getMessage(),
            ]);
        }

        return $state;
    }
}
