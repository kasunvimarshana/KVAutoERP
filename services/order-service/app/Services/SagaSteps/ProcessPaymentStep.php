<?php

namespace App\Services\SagaSteps;

use Shared\Core\Contracts\SagaStepInterface;
use Shared\Core\Services\ExternalServiceClient;
use Illuminate\Support\Facades\Log;

class ProcessPaymentStep implements SagaStepInterface
{
    /**
     * Handle the Saga step
     *
     * @param array $data
     * @return array|bool
     */
    public function handle(array $data): bool|array
    {
        Log::info("Executing ProcessPaymentStep");
        $paymentClient = new ExternalServiceClient('http://payment-service/api/v1/payments');

        $response = $paymentClient->post('/process', [
            'order_id' => $data['order_id'],
            'amount' => $data['total_amount'],
            'currency' => $data['currency_code'],
            'payment_method' => $data['payment_method'] ?? 'default',
        ]);

        if ($response && isset($response['status']) && $response['status'] === 'success') {
            return ['payment_id' => $response['data']['payment_id']];
        }

        return false;
    }

    /**
     * Rollback the Saga step
     *
     * @param array $data
     * @return bool
     */
    public function rollback(array $data): bool
    {
        Log::warning("Rolling back ProcessPaymentStep");
        if (isset($data['payment_id'])) {
            $paymentClient = new ExternalServiceClient('http://payment-service/api/v1/payments');
            $response = $paymentClient->post("/refund/{$data['payment_id']}");
            return $response && isset($response['status']) && $response['status'] === 'success';
        }
        return true;
    }
}
