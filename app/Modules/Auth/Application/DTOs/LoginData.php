<?php
declare(strict_types=1);
namespace Modules\Auth\Application\DTOs;
use Modules\Core\Application\DTOs\BaseDto;

class LoginData extends BaseDto {
    public ?string $email = null;
    public ?string $password = null;
    public bool $remember = false;

    public function rules(): array {
        return [
            'email' => 'required|email',
            'password' => 'required|string',
        ];
    }
}
