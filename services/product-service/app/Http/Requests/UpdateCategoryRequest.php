<?php
namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'parent_id'   => ['nullable', 'string'],
            'name'        => ['sometimes', 'string', 'max:255'],
            'slug'        => ['sometimes', 'string', 'max:100', 'alpha_dash'],
            'description' => ['nullable', 'string'],
            'status'      => ['nullable', 'string', 'in:active,inactive'],
        ];
    }
}
