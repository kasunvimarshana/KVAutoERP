<?php

namespace App\Modules\Product\Resources;

use App\Modules\Inventory\Resources\InventoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'sku' => $this->sku,
            'price' => $this->price,
            'category' => $this->category,
            'tenant_id' => $this->tenant_id,
            'attributes' => $this->attributes,
            'is_active' => $this->is_active,
            'inventory' => $this->whenLoaded('inventory', fn() => new InventoryResource($this->inventory)),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
