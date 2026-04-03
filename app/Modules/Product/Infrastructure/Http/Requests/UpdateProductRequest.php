<?php
declare(strict_types=1);
namespace Modules\Product\Infrastructure\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name'          => 'sometimes|required|string|max:255',
            'price'         => 'sometimes|required|numeric|min:0',
            'currency'      => 'nullable|string|size:3',
            'description'   => 'nullable|string',
            'category'      => 'nullable|string',
            'status'        => 'nullable|string|in:active,inactive',
            'type'          => 'nullable|string|in:physical,service,digital,combo,variable',
            'images'        => 'nullable|array',
            'images.*'      => 'nullable|image|max:5120|mimes:jpg,jpeg,png,gif,webp',
            'primary_image' => 'nullable|integer',
        ];
    }
}
