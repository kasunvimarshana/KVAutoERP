<?php

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
            'name'          => ['sometimes', 'string', 'max:255'],
            'email'         => ['sometimes', 'email', 'max:255'],
            'password'      => ['sometimes', 'string', 'min:8', 'confirmed'],
            'role'          => ['sometimes', 'in:admin,manager,user'],
            'permissions'   => ['sometimes', 'array'],
            'permissions.*' => ['string'],
            'status'        => ['sometimes', 'in:active,inactive,suspended'],
        ];
    }
}
