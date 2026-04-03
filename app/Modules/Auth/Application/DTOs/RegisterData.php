<?php
declare(strict_types=1);
namespace Modules\Auth\Application\DTOs;
use Modules\Core\Application\DTOs\BaseDto;

class RegisterData extends BaseDto {
    public ?int $tenant_id = null;
    public ?string $email = null;
    public ?string $password = null;
    public ?string $first_name = null;
    public ?string $last_name = null;

    public function rules(): array {
        return [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'tenant_id' => 'required|integer',
        ];
    }
}
