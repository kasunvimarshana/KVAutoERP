<?php

namespace App\Services;

use App\Models\StockLedger;
use App\Models\StockLevel;
use App\Models\Warehouse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StockMovementService extends InventoryService
{
    /**
     * Internal stock transfer between warehouses or bins
     */
    public function transfer(array $data): bool
    {
        return DB::transaction(function () use ($data) {
            // 1. Validate compliance for OUT from source
            $this->complianceManager->validateStockOut([
                'product_id' => $data['product_id'],
                'lot_id' => $data['lot_id'] ?? null,
            ]);

            // 2. Record OUT from Source
            $this->recordTransaction([
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['from_warehouse_id'],
                'bin_location_id' => $data['from_bin_id'] ?? null,
                'lot_id' => $data['lot_id'] ?? null,
                'quantity' => -$data['quantity'],
                'uom_id' => $data['uom_id'],
                'transaction_type' => 'Transfer Out',
                'reference_type' => 'Transfer Order',
                'reference_id' => $data['reference_id'] ?? null,
                'cost_at_transaction' => $data['cost'] ?? 0,
            ]);

            // 3. Validate compliance for IN at destination
            $this->complianceManager->validateStockIn([
                'product_id' => $data['product_id'],
                'lot_id' => $data['lot_id'] ?? null,
            ]);

            // 4. Record IN at Destination
            $this->recordTransaction([
                'product_id' => $data['product_id'],
                'warehouse_id' => $data['to_warehouse_id'],
                'bin_location_id' => $data['to_bin_id'] ?? null,
                'lot_id' => $data['lot_id'] ?? null,
                'quantity' => $data['quantity'],
                'uom_id' => $data['uom_id'],
                'transaction_type' => 'Transfer In',
                'reference_type' => 'Transfer Order',
                'reference_id' => $data['reference_id'] ?? null,
                'cost_at_transaction' => $data['cost'] ?? 0,
            ]);

            return true;
        });
    }

    /**
     * Stock adjustment (e.g., from cycle counting or damage)
     */
    public function adjust(array $data): StockLedger
    {
        return $this->recordTransaction([
            'product_id' => $data['product_id'],
            'warehouse_id' => $data['warehouse_id'],
            'bin_location_id' => $data['bin_location_id'] ?? null,
            'lot_id' => $data['lot_id'] ?? null,
            'quantity' => $data['adjustment_quantity'], // Can be pos or neg
            'uom_id' => $data['uom_id'],
            'transaction_type' => $data['reason'] ?? 'Adjustment',
            'reference_type' => 'Adjustment Order',
            'reference_id' => $data['reference_id'] ?? null,
            'cost_at_transaction' => $data['cost'] ?? 0,
        ]);
    }

    /**
     * Stock return (e.g., customer return or supplier return)
     */
    public function return(array $data): StockLedger
    {
        $type = $data['is_customer_return'] ? 'Customer Return' : 'Supplier Return';
        $qty = $data['is_customer_return'] ? $data['quantity'] : -$data['quantity'];

        return $this->recordTransaction([
            'product_id' => $data['product_id'],
            'warehouse_id' => $data['warehouse_id'],
            'bin_location_id' => $data['bin_location_id'] ?? null,
            'lot_id' => $data['lot_id'] ?? null,
            'quantity' => $qty,
            'uom_id' => $data['uom_id'],
            'transaction_type' => $type,
            'reference_type' => 'Return Order',
            'reference_id' => $data['reference_id'] ?? null,
            'cost_at_transaction' => $data['cost'] ?? 0,
        ]);
    }
}
