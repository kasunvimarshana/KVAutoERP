<?php

namespace App\Modules\Order\DTOs;

class OrderDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly array $items,
        public readonly array $shippingAddress,
        public readonly ?array $billingAddress = null,
        public readonly string $currency = 'USD',
        public readonly ?string $notes = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            userId:          (int) $data['user_id'],
            items:           $data['items'],
            shippingAddress: $data['shipping_address'],
            billingAddress:  $data['billing_address'] ?? null,
            currency:        $data['currency'] ?? 'USD',
            notes:           $data['notes'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'user_id'          => $this->userId,
            'items'            => $this->items,
            'shipping_address' => $this->shippingAddress,
            'billing_address'  => $this->billingAddress,
            'currency'         => $this->currency,
            'notes'            => $this->notes,
        ], fn ($v) => $v !== null);
    }
}
