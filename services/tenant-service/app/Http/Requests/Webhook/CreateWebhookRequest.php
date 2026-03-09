<?php

declare(strict_types=1);

namespace App\Http\Requests\Webhook;

use Illuminate\Foundation\Http\FormRequest;

class CreateWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url'         => ['required', 'url', 'max:2048'],
            'events'      => ['required', 'array', 'min:1'],
            'events.*'    => ['required', 'string', 'max:100'],
            'secret'      => ['nullable', 'string', 'min:16', 'max:255'],
            'is_active'   => ['nullable', 'boolean'],
            'retry_count' => ['nullable', 'integer', 'min:1', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'url.required'      => 'A webhook URL is required.',
            'url.url'           => 'The webhook URL must be a valid URL.',
            'events.required'   => 'At least one event is required.',
            'events.min'        => 'At least one event must be specified.',
            'secret.min'        => 'Webhook secret must be at least 16 characters.',
        ];
    }
}
