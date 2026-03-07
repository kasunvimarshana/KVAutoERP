<?php

namespace App\Modules\Order\DTOs;

class OrderDTO
{
    public function __construct(
        public readonly string $tenantId,
        public readonly string $userId,
        public readonly array $items,
        public readonly ?string $notes = null,
        public readonly ?array $shippingAddress = null,
        public readonly ?array $billingAddress = null,
        public readonly string $currency = 'USD',
        public readonly float $discount = 0.00,
        public readonly float $tax = 0.00,
        public readonly ?array $metadata = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            tenantId:        $data['tenant_id'],
            userId:          $data['user_id'],
            items:           $data['items'],
            notes:           $data['notes'] ?? null,
            shippingAddress: $data['shipping_address'] ?? null,
            billingAddress:  $data['billing_address'] ?? null,
            currency:        $data['currency'] ?? 'USD',
            discount:        (float) ($data['discount'] ?? 0.00),
            tax:             (float) ($data['tax'] ?? 0.00),
            metadata:        $data['metadata'] ?? null,
        );
    }
}
