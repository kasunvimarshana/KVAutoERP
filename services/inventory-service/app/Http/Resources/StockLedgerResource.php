<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\StockLedger;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * API Resource transformer for StockLedger entries.
 *
 * @mixin StockLedger
 */
final class StockLedgerResource extends JsonResource
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
            'product_id'       => $this->product_id,
            'warehouse_id'     => $this->warehouse_id,
            'bin_id'           => $this->bin_id,
            'lot_id'           => $this->lot_id,
            'transaction_type' => $this->transaction_type,
            'reference_type'   => $this->reference_type,
            'reference_id'     => $this->reference_id,
            'idempotency_key'  => $this->idempotency_key,
            'qty_change'       => $this->qty_change,
            'qty_after'        => $this->qty_after,
            'unit_cost'        => $this->unit_cost,
            'total_cost'       => $this->total_cost,
            'currency'         => $this->currency,
            'uom_id'           => $this->uom_id,
            'notes'            => $this->notes,
            'performed_by'     => $this->performed_by,
            'transacted_at'    => $this->transacted_at?->toIso8601String(),
            'created_at'       => $this->created_at?->toIso8601String(),
        ];
    }
}
