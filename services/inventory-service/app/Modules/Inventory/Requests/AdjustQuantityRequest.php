<?php

namespace App\Modules\Inventory\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjustQuantityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'delta'  => ['required', 'integer'],
            'reason' => ['sometimes', 'string', 'in:sale,return,adjustment,restock,damage'],
        ];
    }

    public function messages(): array
    {
        return [
            'delta.required' => 'Quantity delta is required.',
            'delta.integer'  => 'Delta must be an integer (positive to add, negative to subtract).',
        ];
    }
}
