<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a single Product model into an API response array.
 *
 * @mixin \App\Models\Product
 */
final class ProductResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'tenant_id'        => $this->tenant_id,
            'organization_id'  => $this->organization_id,
            'branch_id'        => $this->branch_id,
            'sku'              => $this->sku,
            'barcode'          => $this->barcode,
            'barcode_type'     => $this->barcode_type,
            'name'             => $this->name,
            'slug'             => $this->slug,
            'description'      => $this->description,
            'type'             => $this->type,
            'status'           => $this->status,
            'cost_method'      => $this->cost_method,
            'is_serialized'    => $this->is_serialized,
            'is_lot_tracked'   => $this->is_lot_tracked,
            'is_batch_tracked' => $this->is_batch_tracked,
            'has_expiry'       => $this->has_expiry,
            'weight'           => $this->weight,
            'weight_unit'      => $this->weight_unit,
            'dimensions'       => $this->dimensions,
            'metadata'         => $this->metadata,
            'category'         => $this->whenLoaded('category', fn () => new CategoryResource($this->category)),
            'base_uom'         => $this->whenLoaded('baseUom', fn () => new UomResource($this->baseUom)),
            'buying_uom'       => $this->whenLoaded('buyingUom', fn () => new UomResource($this->buyingUom)),
            'selling_uom'      => $this->whenLoaded('sellingUom', fn () => new UomResource($this->sellingUom)),
            'prices'           => $this->whenLoaded('prices', fn () => ProductPriceResource::collection($this->prices)),
            'variants'         => $this->whenLoaded('variants', fn () => $this->variants->map(static fn ($v) => [
                'id'         => $v->id,
                'sku'        => $v->sku,
                'name'       => $v->name,
                'attributes' => $v->attributes,
                'is_active'  => $v->is_active,
            ])),
            'images'           => $this->whenLoaded('productImages', fn () => $this->productImages->map(static fn ($img) => [
                'id'         => $img->id,
                'url'        => $img->url,
                'alt_text'   => $img->alt_text,
                'sort_order' => $img->sort_order,
                'is_primary' => $img->is_primary,
            ])),
            'created_by'  => $this->created_by,
            'updated_by'  => $this->updated_by,
            'created_at'  => $this->created_at?->toIso8601String(),
            'updated_at'  => $this->updated_at?->toIso8601String(),
        ];
    }
}
