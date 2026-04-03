<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Services;
use Modules\Auth\Application\Contracts\AuthUserRepositoryInterface;
use Modules\Auth\Application\Contracts\RegisterUserServiceInterface;

class RegisterUserService implements RegisterUserServiceInterface {
    public function __construct(private AuthUserRepositoryInterface $repo) {}

    public function execute(array $data = []): mixed {
        return null;
    }
}
