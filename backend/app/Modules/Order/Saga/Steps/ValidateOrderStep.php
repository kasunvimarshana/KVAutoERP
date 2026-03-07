<?php

namespace App\Modules\Order\Saga\Steps;

use App\Core\Saga\SagaStep;
use App\Models\Product;

class ValidateOrderStep extends SagaStep
{
    public function getName(): string
    {
        return 'validate_order';
    }

    public function execute(array $context): array
    {
        $items = $context['items'] ?? [];

        if (empty($items)) {
            throw new \InvalidArgumentException('Order must contain at least one item.');
        }

        $totalAmount    = 0;
        $validatedItems = [];

        foreach ($items as $item) {
            $product      = Product::findOrFail($item['product_id']);
            $totalPrice   = (float) $product->price * (int) $item['quantity'];
            $totalAmount += $totalPrice;

            $validatedItems[] = [
                'product_id'  => $product->id,
                'quantity'    => (int) $item['quantity'],
                'unit_price'  => (float) $product->price,
                'total_price' => $totalPrice,
            ];
        }

        return [
            'validated_items' => $validatedItems,
            'total_amount'    => $totalAmount,
        ];
    }

    public function compensate(array $context): void
    {
        // Validation has no side-effects – nothing to roll back.
    }
}
