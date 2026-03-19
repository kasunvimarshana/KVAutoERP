<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates the request body for creating a new permission.
 */
final class StorePermissionRequest extends FormRequest
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
            'name'        => ['required', 'string', 'max:150'],
            'slug'        => ['required', 'string', 'max:150', 'regex:/^[a-z0-9\-\.]+$/'],
            'module'      => ['required', 'string', 'max:100'],
            'action'      => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'metadata'    => ['nullable', 'array'],
        ];
    }
}
