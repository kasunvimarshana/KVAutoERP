<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validation rules for creating a stock reservation.
 */
final class StockReservationRequest extends FormRequest
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
            'product_id'     => 'required|uuid',
            'warehouse_id'   => 'required|uuid',
            'bin_id'         => 'nullable|uuid',
            'lot_id'         => 'nullable|uuid',
            'qty'            => 'required|numeric|min:0.0001',
            'reference_type' => 'required|string|max:60',
            'reference_id'   => 'required|uuid',
            'expires_at'     => 'nullable|date|after:now',
            'notes'          => 'nullable|string',
        ];
    }
}
