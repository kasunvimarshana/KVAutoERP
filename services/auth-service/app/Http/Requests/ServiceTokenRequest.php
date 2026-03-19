<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ServiceTokenRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_id'     => ['required', 'string', 'max:100'],
            'service_secret' => ['required', 'string', 'min:16'],
        ];
    }
}
