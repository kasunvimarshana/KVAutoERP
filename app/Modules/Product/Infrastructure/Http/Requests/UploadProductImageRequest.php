<?php
declare(strict_types=1);
namespace Modules\Product\Infrastructure\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UploadProductImageRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'file'             => 'nullable|image|max:5120|mimes:jpg,jpeg,png,gif,webp',
            'files'            => 'nullable|array',
            'files.*'          => 'image|max:5120|mimes:jpg,jpeg,png,gif,webp',
            'sort_order_start' => 'nullable|integer|min:0',
            'is_primary_index' => 'nullable|integer|min:0',
        ];
    }
}
