<?php

declare(strict_types=1);

namespace App\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'avatar' => ['sometimes', 'nullable', 'url'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:30'],
            'address' => ['sometimes', 'nullable', 'array'],
            'preferences' => ['sometimes', 'nullable', 'array'],
            'notification_settings' => ['sometimes', 'nullable', 'array'],
            'timezone' => ['sometimes', 'nullable', 'string'],
            'locale' => ['sometimes', 'nullable', 'string', 'max:10'],
            'theme' => ['sometimes', 'nullable', 'string', 'in:light,dark,system'],
            'extra_permissions' => ['sometimes', 'nullable', 'array'],
            'extra_permissions.*' => ['string'],
            'is_active' => ['sometimes', 'boolean'],
            'metadata' => ['sometimes', 'nullable', 'array'],
        ];
    }
}
