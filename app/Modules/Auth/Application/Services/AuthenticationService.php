<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Services;
use Modules\Auth\Application\Contracts\AuthenticationServiceInterface;
use Modules\Auth\Application\Contracts\AuthUserRepositoryInterface;

class AuthenticationService implements AuthenticationServiceInterface {
    public function __construct(private AuthUserRepositoryInterface $repo) {}

    public function execute(array $data = []): mixed {
        return null;
    }
}
