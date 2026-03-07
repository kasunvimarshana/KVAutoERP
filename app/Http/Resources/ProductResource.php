<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Service A: Product Resource
 * Formats a product with its related inventory records.
 */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'description' => $this->description,
            'price'       => $this->price,
            'sku'         => $this->sku,
            'created_at'  => $this->created_at,
            'updated_at'  => $this->updated_at,
            // Cross-service relationship: inventory from Service B
            'inventories' => InventoryResource::collection(
                $this->whenLoaded('inventories')
            ),
        ];
    }
}
