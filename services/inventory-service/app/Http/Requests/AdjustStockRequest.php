<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdjustStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantity'       => ['required', 'integer', 'not_in:0'],
            'movement_type'  => ['required', 'in:in,out,adjustment,transfer'],
            'notes'          => ['sometimes', 'nullable', 'string', 'max:500'],
            'reference_type' => ['sometimes', 'nullable', 'string', 'max:100'],
            'reference_id'   => ['sometimes', 'nullable', 'uuid'],
        ];
    }
}
