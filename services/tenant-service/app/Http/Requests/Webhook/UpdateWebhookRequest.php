<?php

declare(strict_types=1);

namespace App\Http\Requests\Webhook;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWebhookRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'url'         => ['sometimes', 'url', 'max:2048'],
            'events'      => ['sometimes', 'array', 'min:1'],
            'events.*'    => ['sometimes', 'string', 'max:100'],
            'secret'      => ['sometimes', 'string', 'min:16', 'max:255'],
            'is_active'   => ['sometimes', 'boolean'],
            'retry_count' => ['sometimes', 'integer', 'min:1', 'max:10'],
        ];
    }
}
