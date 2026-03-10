<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'category_id' => ['nullable', 'string'],
            'name'        => ['required', 'string', 'max:255'],
            'code'        => ['required', 'string', 'max:100'],
            'sku'         => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'price'       => ['required', 'numeric', 'min:0'],
            'cost'        => ['nullable', 'numeric', 'min:0'],
            'unit'        => ['nullable', 'string', 'max:50'],
            'status'      => ['nullable', 'string', 'in:active,inactive,discontinued'],
            'attributes'  => ['nullable', 'array'],
        ];
    }
}
