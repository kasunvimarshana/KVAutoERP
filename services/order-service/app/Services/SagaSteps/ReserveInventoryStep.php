<?php

namespace App\Services\SagaSteps;

use Shared\Core\Contracts\SagaStepInterface;
use Shared\Core\Services\ExternalServiceClient;
use Illuminate\Support\Facades\Log;

class ReserveInventoryStep implements SagaStepInterface
{
    /**
     * Handle the Saga step
     *
     * @param array $data
     * @return array|bool
     */
    public function handle(array $data): bool|array
    {
        Log::info("Executing ReserveInventoryStep");
        $inventoryClient = new ExternalServiceClient('http://inventory-service/api/v1/inventory');

        $response = $inventoryClient->post('/reserve', [
            'order_id' => $data['order_id'],
            'items' => $data['items'],
        ]);

        if ($response && isset($response['status']) && $response['status'] === 'success') {
            return ['reservation_id' => $response['data']['reservation_id']];
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
        Log::warning("Rolling back ReserveInventoryStep");
        if (isset($data['reservation_id'])) {
            $inventoryClient = new ExternalServiceClient('http://inventory-service/api/v1/inventory');
            $response = $inventoryClient->post("/release/{$data['reservation_id']}");
            return $response && isset($response['status']) && $response['status'] === 'success';
        }
        return true;
    }
}
