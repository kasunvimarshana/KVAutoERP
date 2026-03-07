<?php

namespace App\Modules\Order\DTOs;

class OrderWebhookDTO
{
    public function __construct(
        public readonly string $event,
        public readonly string $orderId,
        public readonly string $tenantId,
        public readonly string $orderNumber,
        public readonly string $status,
        public readonly float $total,
        public readonly array $items = [],
        public readonly ?string $timestamp = null,
    ) {}

    public static function fromPayload(array $payload): self
    {
        return new self(
            event:       $payload['event'] ?? '',
            orderId:     $payload['order_id'] ?? '',
            tenantId:    $payload['tenant_id'] ?? '',
            orderNumber: $payload['order_number'] ?? '',
            status:      $payload['status'] ?? '',
            total:       (float) ($payload['total'] ?? 0.00),
            items:       $payload['items'] ?? [],
            timestamp:   $payload['timestamp'] ?? null,
        );
    }
}
