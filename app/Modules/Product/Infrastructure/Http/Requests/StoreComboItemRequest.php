<?php
declare(strict_types=1);
namespace Modules\Product\Infrastructure\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreComboItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['component_product_id' => 'required|integer', 'quantity' => 'required|numeric|min:0.001']; }
}
