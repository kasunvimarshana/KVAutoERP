<?php

namespace App\Modules\Webhook\DTOs;

class WebhookPayloadDTO
{
    public function __construct(
        public readonly string $event,
        public readonly array $payload,
        public readonly string $timestamp,
        public readonly string $webhookId,
        public readonly string $signature
    ) {}

    public static function create(string $event, array $payload, string $secret): self
    {
        $webhookId = uniqid('wh_', true);
        $timestamp = now()->toISOString();
        $body      = json_encode([
            'event'      => $event,
            'payload'    => $payload,
            'timestamp'  => $timestamp,
            'webhook_id' => $webhookId,
        ]);
        $signature = hash_hmac('sha256', $body, $secret);

        return new self(
            event:     $event,
            payload:   $payload,
            timestamp: $timestamp,
            webhookId: $webhookId,
            signature: $signature
        );
    }

    public function toArray(): array
    {
        return [
            'event'      => $this->event,
            'payload'    => $this->payload,
            'timestamp'  => $this->timestamp,
            'webhook_id' => $this->webhookId,
            'signature'  => $this->signature,
        ];
    }
}
