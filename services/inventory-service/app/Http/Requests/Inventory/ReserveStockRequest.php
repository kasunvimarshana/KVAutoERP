<?php

declare(strict_types=1);

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Reserve Stock Request.
 */
class ReserveStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'quantity' => ['required', 'integer', 'min:1'],
            'order_id' => ['required', 'string', 'uuid'],
        ];
    }
}
