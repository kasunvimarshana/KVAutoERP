<?php

namespace App\Application\Saga\Steps;

use App\Application\Saga\SagaState;
use App\Application\Saga\SagaStep;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReserveInventoryStep extends SagaStep
{
    public function __construct(private readonly string $inventoryServiceUrl) {}

    public function getName(): string
    {
        return 'reserve_inventory';
    }

    public function execute(SagaState $state): SagaState
    {
        $payload      = $state->getPayload();
        $reservations = [];

        foreach ($payload['items'] as $item) {
            $response = Http::withHeaders([
                'X-Tenant-ID'   => $payload['tenant_id'],
                'Authorization' => 'Bearer ' . ($payload['auth_token'] ?? ''),
                'Accept'        => 'application/json',
            ])->timeout(10)->post(
                "{$this->inventoryServiceUrl}/api/v1/inventories/{$item['inventory_id']}/reserve",
                [
                    'quantity' => $item['quantity'],
                    'saga_id'  => $state->getSagaId(),
                    'order_id' => $payload['order_number'] ?? null,
                ]
            );

            if (!$response->successful()) {
                throw new \RuntimeException(
                    "Failed to reserve inventory for item [{$item['inventory_id']}]: " . $response->body()
                );
            }

            $reservations[] = [
                'inventory_id'   => $item['inventory_id'],
                'quantity'       => $item['quantity'],
                'reservation_id' => $response->json('reservation_id'),
            ];

            Log::info("Saga [{$state->getSagaId()}]: Reserved inventory [{$item['inventory_id']}]", [
                'reservation_id' => $response->json('reservation_id'),
            ]);
        }

        return $this->setContextValue($state, 'reservations', $reservations);
    }

    public function compensate(SagaState $state): SagaState
    {
        $context = $state->getContext();
        $payload = $state->getPayload();

        foreach ($context['reservations'] ?? [] as $reservation) {
            try {
                Http::withHeaders([
                    'X-Tenant-ID'   => $payload['tenant_id'],
                    'Authorization' => 'Bearer ' . ($payload['auth_token'] ?? ''),
                    'Accept'        => 'application/json',
                ])->timeout(10)->post(
                    "{$this->inventoryServiceUrl}/api/v1/inventories/{$reservation['inventory_id']}/release",
                    [
                        'quantity'       => $reservation['quantity'],
                        'reservation_id' => $reservation['reservation_id'],
                        'saga_id'        => $state->getSagaId(),
                    ]
                );

                Log::info("Saga [{$state->getSagaId()}]: Released reservation [{$reservation['reservation_id']}]");
            } catch (\Throwable $e) {
                Log::error("Saga [{$state->getSagaId()}]: Failed to release reservation [{$reservation['reservation_id']}]", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $state;
    }
}
