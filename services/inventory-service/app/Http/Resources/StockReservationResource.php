<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\StockReservation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource transformer for StockReservation.
 *
 * @mixin StockReservation
 */
final class StockReservationResource extends JsonResource
{
    /**
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'tenant_id'      => $this->tenant_id,
            'product_id'     => $this->product_id,
            'warehouse_id'   => $this->warehouse_id,
            'bin_id'         => $this->bin_id,
            'lot_id'         => $this->lot_id,
            'reference_type' => $this->reference_type,
            'reference_id'   => $this->reference_id,
            'qty_reserved'   => $this->qty_reserved,
            'qty_fulfilled'  => $this->qty_fulfilled,
            'qty_remaining'  => $this->qty_remaining,
            'status'         => $this->status,
            'expires_at'     => $this->expires_at?->toIso8601String(),
            'notes'          => $this->notes,
            'created_at'     => $this->created_at?->toIso8601String(),
            'updated_at'     => $this->updated_at?->toIso8601String(),
        ];
    }
}
