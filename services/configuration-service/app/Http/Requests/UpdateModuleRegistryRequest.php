<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateModuleRegistryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'module_name'   => ['sometimes', 'string', 'max:200'],
            'module_key'    => ['sometimes', 'string', 'max:200', 'regex:/^[a-z0-9._-]+$/'],
            'is_enabled'    => ['boolean'],
            'configuration' => ['nullable', 'array'],
            'dependencies'  => ['nullable', 'array'],
            'dependencies.*' => ['string', 'max:200'],
            'version'       => ['nullable', 'string', 'max:50'],
            'metadata'      => ['nullable', 'array'],
        ];
    }
}
