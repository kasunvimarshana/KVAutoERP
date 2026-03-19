<?php

namespace App\Services;

use App\Models\ReorderRule;
use App\Models\StockLevel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ProcurementSuggestionService
{
    /**
     * Checks all reorder rules and returns suggestions for products below minimum.
     */
    public function getSuggestions(): Collection
    {
        $rules = ReorderRule::where('status', 'Active')->get();
        $suggestions = collect();

        foreach ($rules as $rule) {
            $currentStock = StockLevel::where('product_id', $rule->product_id)
                ->where('warehouse_id', $rule->warehouse_id)
                ->sum('available_quantity');

            if ($currentStock < $rule->min_quantity) {
                $suggestedQty = $rule->max_quantity - $currentStock;
                
                $suggestions->push([
                    'product_id' => $rule->product_id,
                    'warehouse_id' => $rule->warehouse_id,
                    'current_stock' => $currentStock,
                    'min_quantity' => $rule->min_quantity,
                    'suggested_reorder_qty' => max($suggestedQty, $rule->reorder_quantity),
                    'priority' => ($currentStock <= 0) ? 'Critical' : 'High',
                ]);
                
                Log::info("Procurement suggestion generated for Product: {$rule->product_id} in WH: {$rule->warehouse_id}");
            }
        }

        return $suggestions;
    }
}
