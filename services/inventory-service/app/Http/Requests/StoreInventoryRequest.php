<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreInventoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'product_id'       => ['required', 'string'],
            'product_code'     => ['nullable', 'string'],
            'product_name'     => ['nullable', 'string'],
            'category_id'      => ['nullable', 'string'],
            'quantity_on_hand' => ['required', 'integer', 'min:0'],
            'quantity_reserved'=> ['nullable', 'integer', 'min:0'],
            'reorder_point'    => ['nullable', 'integer', 'min:0'],
            'reorder_quantity' => ['nullable', 'integer', 'min:0'],
            'location'         => ['nullable', 'string', 'max:255'],
            'status'           => ['nullable', 'string', 'in:active,inactive'],
        ];
    }
}
