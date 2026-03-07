<?php

namespace App\Modules\Inventory\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|integer|exists:products,id',
            'tenant_id' => 'required|integer|exists:tenants,id',
            'quantity' => 'sometimes|integer|min:0',
            'reserved_quantity' => 'sometimes|integer|min:0',
            'min_quantity' => 'sometimes|integer|min:0',
            'max_quantity' => 'sometimes|integer|min:0',
            'location' => 'sometimes|string|max:255',
            'notes' => 'sometimes|string',
        ];
    }
}
