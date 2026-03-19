<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Transforms a UnitOfMeasure model into an API response array.
 *
 * @mixin \App\Models\UnitOfMeasure
 */
final class UomResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'tenant_id'    => $this->tenant_id,
            'name'         => $this->name,
            'symbol'       => $this->symbol,
            'category'     => $this->category,
            'is_base_unit' => $this->is_base_unit,
            'created_by'   => $this->created_by,
            'updated_by'   => $this->updated_by,
            'created_at'   => $this->created_at?->toIso8601String(),
            'updated_at'   => $this->updated_at?->toIso8601String(),
        ];
    }
}
