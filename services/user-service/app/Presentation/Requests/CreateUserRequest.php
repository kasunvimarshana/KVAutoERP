<?php

declare(strict_types=1);

namespace App\Presentation\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required'],
            'avatar' => ['nullable', 'url'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'array'],
            'address.street' => ['nullable', 'string'],
            'address.city' => ['nullable', 'string'],
            'address.state' => ['nullable', 'string'],
            'address.country' => ['nullable', 'string'],
            'address.postal_code' => ['nullable', 'string'],
            'preferences' => ['nullable', 'array'],
            'notification_settings' => ['nullable', 'array'],
            'timezone' => ['nullable', 'string', 'timezone:all'],
            'locale' => ['nullable', 'string', 'max:10'],
            'theme' => ['nullable', 'string', 'in:light,dark,system'],
            'extra_permissions' => ['nullable', 'array'],
            'extra_permissions.*' => ['string'],
            'is_active' => ['nullable', 'boolean'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
