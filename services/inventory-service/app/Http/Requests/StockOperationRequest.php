<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StockOperationRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'product_id' => ['required', 'string'],
            'quantity'   => ['required', 'integer', 'min:1'],
            'order_id'   => ['required', 'string'],
        ];
    }
}
