<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the request body for creating a new role.
 */
final class StoreRoleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name'            => ['required', 'string', 'max:100'],
            'slug'            => ['required', 'string', 'max:100', 'regex:/^[a-z0-9\-]+$/'],
            'description'     => ['nullable', 'string', 'max:500'],
            'hierarchy_level' => ['nullable', 'integer', 'min:0', 'max:100'],
            'metadata'        => ['nullable', 'array'],
        ];
    }
}
