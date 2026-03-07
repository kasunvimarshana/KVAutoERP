<?php
namespace App\Http\Requests;

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
            'product_id'          => ['required', 'uuid'],
            'warehouse_location'  => ['sometimes', 'nullable', 'string', 'max:255'],
            'quantity'            => ['required', 'integer', 'min:0'],
            'reserved_quantity'   => ['sometimes', 'integer', 'min:0'],
            'unit'                => ['sometimes', 'nullable', 'string', 'max:50'],
            'min_level'           => ['required', 'integer', 'min:0'],
            'max_level'           => ['required', 'integer', 'min:0'],
            'status'              => ['sometimes', 'in:active,inactive'],
            'notes'               => ['sometimes', 'nullable', 'string'],
        ];
    }
}
