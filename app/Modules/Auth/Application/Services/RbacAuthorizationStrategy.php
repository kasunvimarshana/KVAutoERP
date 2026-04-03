<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Services;
use Modules\Auth\Application\Contracts\AuthorizationStrategyInterface;
use Modules\Auth\Application\Contracts\AuthUserRepositoryInterface;

class RbacAuthorizationStrategy implements AuthorizationStrategyInterface {
    public function __construct(private AuthUserRepositoryInterface $repo) {}

    public function getName(): string { return 'rbac'; }

    public function can(int $userId, string $ability, mixed $subject = null): bool {
        return false;
    }
}
