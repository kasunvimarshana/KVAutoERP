<?php
declare(strict_types=1);
namespace Modules\Category\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name'        => 'sometimes|required|string|max:255',
            'slug'        => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'parent_id'   => 'nullable|integer',
            'status'      => 'nullable|string|in:active,inactive',
            'depth'       => 'nullable|integer',
            'path'        => 'nullable|string',
        ];
    }
}
