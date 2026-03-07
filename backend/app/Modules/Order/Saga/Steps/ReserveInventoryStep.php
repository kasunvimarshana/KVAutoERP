<?php

namespace App\Modules\Order\Saga\Steps;

use App\Core\Saga\SagaStep;
use App\Models\Inventory;

class ReserveInventoryStep extends SagaStep
{
    /** @var array<int,array{inventory_id:int,quantity:int}> */
    protected array $reservations = [];

    public function getName(): string
    {
        return 'reserve_inventory';
    }

    public function execute(array $context): array
    {
        $this->reservations = [];
        $items = $context['validated_items'] ?? [];

        foreach ($items as $item) {
            $inventory = Inventory::where('product_id', $item['product_id'])->first();

            if (!$inventory) {
                throw new \RuntimeException(
                    "No inventory record found for product #{$item['product_id']}."
                );
            }

            $available = $inventory->quantity - $inventory->reserved_quantity;

            if ($available < $item['quantity']) {
                throw new \RuntimeException(
                    "Insufficient stock for product #{$item['product_id']}. " .
                    "Available: {$available}, requested: {$item['quantity']}."
                );
            }

            $inventory->increment('reserved_quantity', $item['quantity']);

            $this->reservations[] = [
                'inventory_id' => $inventory->id,
                'quantity'     => $item['quantity'],
            ];
        }

        return ['reservations' => $this->reservations];
    }

    public function compensate(array $context): void
    {
        foreach ($this->reservations as $reservation) {
            $inventory = Inventory::find($reservation['inventory_id']);
            $inventory?->decrement('reserved_quantity', $reservation['quantity']);
        }
    }
}
