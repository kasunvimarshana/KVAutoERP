<?php
declare(strict_types=1);
namespace Modules\Product\Infrastructure\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class StoreProductVariationRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['sku' => 'required|string', 'name' => 'required|string', 'price' => 'required|numeric|min:0']; }
}
