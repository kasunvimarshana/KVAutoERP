<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Services;
use Modules\Auth\Application\Contracts\AuthUserRepositoryInterface;
use Modules\Auth\Application\Contracts\LoginServiceInterface;
use Modules\Auth\Application\Contracts\TokenServiceInterface;

class LoginService implements LoginServiceInterface {
    public function __construct(
        private AuthUserRepositoryInterface $repo,
        private TokenServiceInterface $tokenService
    ) {}

    public function execute(array $data = []): mixed {
        return null;
    }
}
