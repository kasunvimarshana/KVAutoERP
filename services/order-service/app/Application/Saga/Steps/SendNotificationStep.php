<?php

namespace App\Application\Saga\Steps;

use App\Application\Saga\SagaState;
use App\Application\Saga\SagaStep;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendNotificationStep extends SagaStep
{
    public function __construct(private readonly string $notificationServiceUrl) {}

    public function getName(): string
    {
        return 'send_notification';
    }

    /**
     * Notifications are fire-and-forget; failure here does not fail the saga.
     */
    public function execute(SagaState $state): SagaState
    {
        $payload = $state->getPayload();
        $context = $state->getContext();

        $notificationPayload = [
            'type'          => 'order_created',
            'tenant_id'     => $payload['tenant_id'],
            'order_id'      => $context['order_id']     ?? null,
            'order_number'  => $context['order_number'] ?? null,
            'customer_id'   => $payload['customer_id'],
            'customer_name' => $payload['customer_name'],
            'customer_email'=> $payload['customer_email'],
            'total'         => $payload['total'],
            'items_count'   => count($payload['items']),
            'saga_id'       => $state->getSagaId(),
            'channels'      => ['email', 'in_app'],
        ];

        try {
            $response = Http::withHeaders([
                'X-Tenant-ID'   => $payload['tenant_id'],
                'Authorization' => 'Bearer ' . ($payload['auth_token'] ?? ''),
                'Accept'        => 'application/json',
            ])->timeout(5)->post(
                "{$this->notificationServiceUrl}/api/v1/notifications/send",
                $notificationPayload
            );

            if ($response->successful()) {
                Log::info("Saga [{$state->getSagaId()}]: Notification sent", [
                    'notification_id' => $response->json('notification_id'),
                ]);
                $state = $this->setContextValue($state, 'notification_id', $response->json('notification_id'));
            } else {
                Log::warning("Saga [{$state->getSagaId()}]: Notification service returned non-success", [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            }
        } catch (\Throwable $e) {
            // Non-critical – log and continue
            Log::warning("Saga [{$state->getSagaId()}]: Notification failed (non-critical)", [
                'error' => $e->getMessage(),
            ]);
        }

        return $state;
    }

    /**
     * Notifications cannot be "unsent"; skip compensation entirely.
     */
    public function compensate(SagaState $state): SagaState
    {
        return $state;
    }

    public function canCompensate(): bool
    {
        return false;
    }
}
