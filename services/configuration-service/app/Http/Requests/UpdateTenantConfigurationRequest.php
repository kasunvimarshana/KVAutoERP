<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantConfigurationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'service_name' => ['sometimes', 'string', 'max:100'],
            'config_key'   => ['sometimes', 'string', 'max:200', 'regex:/^[a-z0-9._-]+$/'],
            'config_value' => ['sometimes', 'array'],
            'config_type'  => ['sometimes', 'string', 'in:string,json,boolean,integer'],
            'is_encrypted' => ['boolean'],
            'is_active'    => ['boolean'],
            'description'  => ['nullable', 'string', 'max:500'],
            'metadata'     => ['nullable', 'array'],
        ];
    }
}
