<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Stock movement representation.
 */
class StockMovementResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $data = is_array($this->resource) ? $this->resource : $this->resource->toArray();

        return [
            'id'                => $data['id'],
            'product_id'        => $data['product_id'],
            'tenant_id'         => $data['tenant_id'],
            'type'              => [
                'value' => $data['type'] instanceof \App\Domain\Inventory\Enums\StockMovementType
                    ? $data['type']->value
                    : $data['type'],
                'label' => $data['type_label'] ?? ucfirst($data['type'] ?? ''),
            ],
            'quantity'          => (int) $data['quantity'],
            'reference'         => $data['reference'] ?? null,
            'reason'            => $data['reason'] ?? null,
            'previous_quantity' => (int) ($data['previous_quantity'] ?? 0),
            'new_quantity'      => (int) ($data['new_quantity'] ?? 0),
            'net_change'        => (int) ($data['new_quantity'] ?? 0) - (int) ($data['previous_quantity'] ?? 0),
            'performed_by'      => $data['performed_by'] ?? null,
            'created_at'        => $data['created_at'] ?? null,
        ];
    }
}
