<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation rules for transferring stock between warehouses/bins.
 */
final class StockTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'from_warehouse_id'    => 'required|uuid',
            'from_bin_id'          => 'nullable|uuid',
            'to_warehouse_id'      => 'required|uuid',
            'to_bin_id'            => 'nullable|uuid',
            'transfer_type'        => 'nullable|string|in:internal,cross_branch,drop_ship',
            'transfer_number'      => 'nullable|string|max:50',
            'notes'                => 'nullable|string',
            'lines'                => 'required|array|min:1',
            'lines.*.product_id'   => 'required|uuid',
            'lines.*.qty'          => 'required|numeric|min:0.0001',
            'lines.*.lot_id'       => 'nullable|uuid',
            'lines.*.unit_cost'    => 'nullable|numeric|min:0',
        ];
    }
}
