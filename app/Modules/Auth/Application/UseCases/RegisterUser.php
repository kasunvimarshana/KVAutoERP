<?php
declare(strict_types=1);
namespace Modules\Auth\Application\UseCases;
use Modules\Auth\Application\Contracts\LoginServiceInterface;
use Modules\Auth\Application\Contracts\RegisterUserServiceInterface;

class RegisterUser {
    public function __construct(
        private RegisterUserServiceInterface $register,
        private LoginServiceInterface $login
    ) {}

    public function execute(array $data = []): mixed {
        return null;
    }
}
