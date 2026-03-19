<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation rules for dispatching stock from a warehouse.
 */
final class StockDispatchRequest extends FormRequest
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
            'product_id'      => 'required|uuid',
            'warehouse_id'    => 'required|uuid',
            'bin_id'          => 'nullable|uuid',
            'lot_id'          => 'nullable|uuid',
            'qty'             => 'required|numeric|min:0.0001',
            'unit_cost'       => 'nullable|numeric|min:0',
            'currency'        => 'nullable|string|size:3',
            'uom_id'          => 'nullable|uuid',
            'reference_type'  => 'nullable|string|max:60',
            'reference_id'    => 'nullable|uuid',
            'idempotency_key' => 'nullable|string|max:100',
            'notes'           => 'nullable|string',
            'metadata'        => 'nullable|array',
            'transacted_at'   => 'nullable|date',
        ];
    }
}
