<?php

declare(strict_types=1);

namespace App\Presentation\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'tenant_id' => $this->tenant_id,
            'name' => $this->name,
            'code' => $this->code,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'description' => $this->whenNotNull($this->description),
            'price' => (float) $this->price,
            'cost_price' => $this->cost_price ? (float) $this->cost_price : null,
            'compare_price' => $this->compare_price ? (float) $this->compare_price : null,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'unit' => $this->unit,
            'weight' => $this->weight ? (float) $this->weight : null,
            'dimensions' => $this->dimensions,
            'images' => $this->images ?? [],
            'attributes' => $this->attributes ?? [],
            'tags' => $this->tags ?? [],
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'category' => $this->whenLoaded('category', fn() => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
                'slug' => $this->category?->slug,
            ]),
            'variants' => $this->whenLoaded('variants', fn() => VariantResource::collection($this->variants)),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
