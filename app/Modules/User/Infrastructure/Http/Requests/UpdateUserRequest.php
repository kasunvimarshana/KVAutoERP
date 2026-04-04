<?php

declare(strict_types=1);

namespace Modules\User\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'name'     => ['sometimes', 'string', 'max:255'],
            'email'    => ['sometimes', 'email', "unique:users,email,{$userId}"],
            'timezone' => ['sometimes', 'string', 'timezone:all'],
            'locale'   => ['sometimes', 'string', 'max:10'],
            'status'   => ['sometimes', 'string', 'in:active,inactive'],
        ];
    }
}
