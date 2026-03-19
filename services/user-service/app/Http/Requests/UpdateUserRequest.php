<?php

declare(strict_types=1);

namespace App\Http\Requests;

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
            'name'            => ['sometimes', 'string', 'max:255'],
            'email'           => ['sometimes', 'email'],
            'status'          => ['sometimes', 'in:active,inactive,suspended'],
            'phone'           => ['sometimes', 'string', 'max:50'],
            'organization_id' => ['sometimes', 'uuid'],
            'branch_id'       => ['sometimes', 'uuid'],
        ];
    }
}
