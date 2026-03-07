<?php

namespace App\Modules\Order\DTOs;

class OrderDTO
{
    public function __construct(
        public readonly ?int $tenantId = null,
        public readonly ?int $userId = null,
        public readonly ?string $status = null,
        public readonly ?array $items = null,
        public readonly ?string $notes = null,
        public readonly ?array $metadata = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            tenantId: $data['tenant_id'] ?? null,
            userId: $data['user_id'] ?? null,
            status: $data['status'] ?? null,
            items: $data['items'] ?? null,
            notes: $data['notes'] ?? null,
            metadata: $data['metadata'] ?? null,
        );
    }
}
