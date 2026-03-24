<?php

namespace Modules\Auth\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class LoginData extends BaseDto
{
    public string $email = '';
    public string $password = '';

    public function rules(): array
    {
        return [
            'email'    => ['required', 'string', 'email'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }
}
