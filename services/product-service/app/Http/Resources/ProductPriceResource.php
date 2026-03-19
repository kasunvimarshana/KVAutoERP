<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a ProductPrice model into an API response array.
 *
 * Prices are returned as strings to preserve the BCMath decimal precision.
 *
 * @mixin \App\Models\ProductPrice
 */
final class ProductPriceResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'tenant_id'     => $this->tenant_id,
            'product_id'    => $this->product_id,
            'currency_code' => $this->currency_code,
            'price_type'    => $this->price_type,
            'tier_min_qty'  => $this->tier_min_qty,
            'price'         => $this->price,
            'valid_from'    => $this->valid_from?->toDateString(),
            'valid_to'      => $this->valid_to?->toDateString(),
            'location_id'   => $this->location_id,
            'created_by'    => $this->created_by,
            'updated_by'    => $this->updated_by,
            'created_at'    => $this->created_at?->toIso8601String(),
            'updated_at'    => $this->updated_at?->toIso8601String(),
        ];
    }
}
