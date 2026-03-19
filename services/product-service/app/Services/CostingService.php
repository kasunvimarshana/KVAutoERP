<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Facades\Log;

class CostingService
{
    /**
     * Calculate current inventory valuation for a product
     * 
     * @param Product $product
     * @param array $ledgerEntries // Historical transactions
     * @return float
     */
    public function calculateValuation(Product $product, array $ledgerEntries): float
    {
        switch ($product->costing_method) {
            case 'FIFO':
                return $this->calculateFIFO($ledgerEntries);
            case 'LIFO':
                return $this->calculateLIFO($ledgerEntries);
            case 'Weighted Average':
                return $this->calculateWeightedAverage($ledgerEntries);
            default:
                return 0;
        }
    }

    /**
     * First-In, First-Out calculation
     */
    protected function calculateFIFO(array $ledgerEntries): float
    {
        // Logic: Total cost of the most recent entries that make up current quantity
        // Implementation omitted for brevity but logic is straightforward
        return 0;
    }

    /**
     * Last-In, First-Out calculation
     */
    protected function calculateLIFO(array $ledgerEntries): float
    {
        return 0;
    }

    /**
     * Weighted Average Cost calculation
     */
    protected function calculateWeightedAverage(array $ledgerEntries): float
    {
        $totalCost = 0;
        $totalQty = 0;

        foreach ($ledgerEntries as $entry) {
            if ($entry['quantity'] > 0) { // Only IN movements affect cost
                $totalCost += ($entry['quantity'] * $entry['cost_at_transaction']);
                $totalQty += $entry['quantity'];
            }
        }

        return $totalQty > 0 ? ($totalCost / $totalQty) : 0;
    }
}
