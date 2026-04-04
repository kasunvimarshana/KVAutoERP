<?php

declare(strict_types=1);

namespace Modules\Configuration\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSystemConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'key'         => ['required', 'string', 'max:255'],
            'value'       => ['sometimes', 'nullable', 'string'],
            'tenant_id'   => ['sometimes', 'nullable', 'integer'],
            'group'       => ['sometimes', 'string', 'max:100'],
            'description' => ['sometimes', 'nullable', 'string'],
        ];
    }
}
