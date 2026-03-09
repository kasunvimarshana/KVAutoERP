<?php

declare(strict_types=1);

namespace App\Http\Resources\Inventory;

use App\Domain\Inventory\Entities\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Inventory Item Resource.
 *
 * @mixin InventoryItem
 */
class InventoryItemResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'tenant_id'           => $this->tenant_id,
            'sku'                 => $this->sku,
            'name'                => $this->name,
            'description'         => $this->description,
            'quantity'            => $this->quantity,
            'reserved_quantity'   => $this->reserved_quantity,
            'available_quantity'  => $this->available_quantity,
            'reorder_point'       => $this->reorder_point,
            'reorder_quantity'    => $this->reorder_quantity,
            'needs_reorder'       => $this->needsReorder(),
            'unit_cost'           => $this->unit_cost,
            'unit_price'          => $this->unit_price,
            'unit_of_measure'     => $this->unit_of_measure,
            'status'              => $this->status,
            'tags'                => $this->tags ?? [],
            'metadata'            => $this->metadata,
            'category'            => $this->whenLoaded('category', fn () => [
                'id'   => $this->category->id,
                'name' => $this->category->name,
            ]),
            'warehouse'           => $this->whenLoaded('warehouse', fn () => [
                'id'   => $this->warehouse->id,
                'name' => $this->warehouse->name,
            ]),
            'created_at'          => $this->created_at?->toISOString(),
            'updated_at'          => $this->updated_at?->toISOString(),
        ];
    }
}
