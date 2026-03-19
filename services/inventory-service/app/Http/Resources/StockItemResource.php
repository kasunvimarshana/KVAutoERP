<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\StockItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource transformer for StockItem.
 *
 * @mixin StockItem
 */
final class StockItemResource extends JsonResource
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
            'warehouse_id'  => $this->warehouse_id,
            'bin_id'        => $this->bin_id,
            'lot_id'        => $this->lot_id,
            'qty_on_hand'   => $this->qty_on_hand,
            'qty_reserved'  => $this->qty_reserved,
            'qty_available' => $this->qty_available,
            'uom_id'        => $this->uom_id,
            'unit_cost'     => $this->unit_cost,
            'version'       => $this->version,
            'warehouse'     => $this->whenLoaded('warehouse', fn () => [
                'id'   => $this->warehouse?->id,
                'code' => $this->warehouse?->code,
                'name' => $this->warehouse?->name,
            ]),
            'bin'           => $this->whenLoaded('bin', fn () => $this->bin ? [
                'id'   => $this->bin->id,
                'code' => $this->bin->code,
            ] : null),
            'lot'           => $this->whenLoaded('lot', fn () => $this->lot ? [
                'id'          => $this->lot->id,
                'lot_number'  => $this->lot->lot_number,
                'expiry_date' => $this->lot->expiry_date?->toDateString(),
            ] : null),
            'updated_at'    => $this->updated_at?->toIso8601String(),
        ];
    }
}
