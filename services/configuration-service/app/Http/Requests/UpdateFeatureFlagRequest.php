<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFeatureFlagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'flag_key'           => ['sometimes', 'string', 'max:200', 'regex:/^[a-z0-9._-]+$/'],
            'is_enabled'         => ['boolean'],
            'rollout_percentage' => ['integer', 'min:0', 'max:100'],
            'conditions'         => ['nullable', 'array'],
            'description'        => ['nullable', 'string', 'max:500'],
            'metadata'           => ['nullable', 'array'],
        ];
    }
}
