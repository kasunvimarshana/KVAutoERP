<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Services;
use Modules\Auth\Application\Contracts\AuthorizationServiceInterface;
use Modules\Auth\Application\Contracts\AuthorizationStrategyInterface;
use Modules\Auth\Application\Contracts\AuthUserRepositoryInterface;

class AuthorizationService implements AuthorizationServiceInterface {
    public function __construct(
        private AuthUserRepositoryInterface $repo,
        AuthorizationStrategyInterface ...$strategies
    ) {}

    public function can(int $userId, string $ability, mixed $subject = null): bool {
        return false;
    }
}
