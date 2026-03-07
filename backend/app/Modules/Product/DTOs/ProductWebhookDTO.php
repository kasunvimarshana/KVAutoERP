<?php

namespace App\Modules\Product\DTOs;

class ProductWebhookDTO
{
    public function __construct(
        public readonly string $event,
        public readonly string $productId,
        public readonly string $tenantId,
        public readonly ?string $sku = null,
        public readonly array $data = [],
        public readonly ?string $timestamp = null,
    ) {}

    public static function fromPayload(array $payload): self
    {
        return new self(
            event:     $payload['event'] ?? '',
            productId: $payload['product_id'] ?? '',
            tenantId:  $payload['tenant_id'] ?? '',
            sku:       $payload['sku'] ?? null,
            data:      $payload['data'] ?? [],
            timestamp: $payload['timestamp'] ?? null,
        );
    }
}
