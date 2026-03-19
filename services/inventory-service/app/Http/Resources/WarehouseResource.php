<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource transformer for Warehouse.
 *
 * @mixin Warehouse
 */
final class WarehouseResource extends JsonResource
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
            'organization_id' => $this->organization_id,
            'branch_id'     => $this->branch_id,
            'code'          => $this->code,
            'name'          => $this->name,
            'description'   => $this->description,
            'type'          => $this->type,
            'status'        => $this->status,
            'address' => [
                'line1'       => $this->address_line1,
                'line2'       => $this->address_line2,
                'city'        => $this->city,
                'state'       => $this->state,
                'country'     => $this->country,
                'postal_code' => $this->postal_code,
            ],
            'is_default'    => $this->is_default,
            'metadata'      => $this->metadata,
            'created_at'    => $this->created_at?->toIso8601String(),
            'updated_at'    => $this->updated_at?->toIso8601String(),
        ];
    }
}
