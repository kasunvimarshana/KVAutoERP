<?php

namespace App\Modules\User\Webhooks;

use App\Modules\User\DTOs\WebhookPayloadDTO;
use Illuminate\Http\Request;

class UserWebhookHandler
{
    public function handle(Request $request): array
    {
        $payload = WebhookPayloadDTO::fromRequest($request);

        return match ($payload->event) {
            'user.created' => $this->handleUserCreated($payload),
            'user.updated' => $this->handleUserUpdated($payload),
            'user.deleted' => $this->handleUserDeleted($payload),
            default => ['status' => 'ignored', 'event' => $payload->event],
        };
    }

    private function handleUserCreated(WebhookPayloadDTO $payload): array
    {
        \Illuminate\Support\Facades\Log::info('Webhook: user.created', ['data' => $payload->data]);
        return ['status' => 'processed', 'event' => 'user.created'];
    }

    private function handleUserUpdated(WebhookPayloadDTO $payload): array
    {
        \Illuminate\Support\Facades\Log::info('Webhook: user.updated', ['data' => $payload->data]);
        return ['status' => 'processed', 'event' => 'user.updated'];
    }

    private function handleUserDeleted(WebhookPayloadDTO $payload): array
    {
        \Illuminate\Support\Facades\Log::info('Webhook: user.deleted', ['data' => $payload->data]);
        return ['status' => 'processed', 'event' => 'user.deleted'];
    }
}
