<?php
declare(strict_types=1);
namespace Modules\Auth\Application\DTOs;

use Modules\Core\Application\DTOs\BaseDto;

class LoginDto extends BaseDto
{
    public string $email;
    public string $password;
    public ?int $tenantId = null;

    public function rules(): array
    {
        return [
            'email'    => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ];
    }
}
