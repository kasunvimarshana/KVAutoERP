<?php

declare(strict_types=1);

namespace App\Http\Resources\Order;

use App\Domain\Order\Entities\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Order Resource.
 *
 * @mixin Order
 */
class OrderResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'tenant_id'           => $this->tenant_id,
            'customer_id'         => $this->customer_id,
            'status'              => $this->status,
            'saga_status'         => $this->saga_status,
            'saga_transaction_id' => $this->saga_transaction_id,
            'subtotal'            => $this->subtotal,
            'tax_amount'          => $this->tax_amount,
            'total_amount'        => $this->total_amount,
            'currency'            => $this->currency,
            'notes'               => $this->notes,
            'items'               => $this->whenLoaded('items', fn () =>
                $this->items->map(fn ($item) => [
                    'id'                 => $item->id,
                    'inventory_item_id'  => $item->inventory_item_id,
                    'sku'                => $item->sku,
                    'name'               => $item->name,
                    'quantity'           => $item->quantity,
                    'unit_price'         => $item->unit_price,
                    'total_price'        => $item->total_price,
                ]),
            ),
            'saga_log'            => $this->whenLoaded('sagaLog', fn () =>
                $this->sagaLog->map(fn ($log) => [
                    'step'    => $log->step_name,
                    'action'  => $log->action,
                    'status'  => $log->status,
                    'created_at' => $log->created_at?->toISOString(),
                ]),
            ),
            'fulfilled_at'        => $this->fulfilled_at?->toISOString(),
            'cancelled_at'        => $this->cancelled_at?->toISOString(),
            'created_at'          => $this->created_at?->toISOString(),
            'updated_at'          => $this->updated_at?->toISOString(),
        ];
    }
}
