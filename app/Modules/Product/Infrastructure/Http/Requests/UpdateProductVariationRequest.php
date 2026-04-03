<?php
declare(strict_types=1);
namespace Modules\Product\Infrastructure\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UpdateProductVariationRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array { return ['name' => 'sometimes|required|string', 'price' => 'sometimes|required|numeric|min:0']; }
}
