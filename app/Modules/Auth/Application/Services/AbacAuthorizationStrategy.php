<?php
declare(strict_types=1);
namespace Modules\Auth\Application\Services;
use Illuminate\Support\Facades\Gate;
use Modules\Auth\Application\Contracts\AuthorizationStrategyInterface;
use Modules\Auth\Application\Contracts\AuthUserRepositoryInterface;

class AbacAuthorizationStrategy implements AuthorizationStrategyInterface {
    public function __construct(private AuthUserRepositoryInterface $repo) {}

    public function getName(): string { return 'abac'; }

    public function can(int $userId, string $ability, mixed $subject = null): bool {
        try {
            return Gate::forUser($userId)->allows($ability, $subject ?? []);
        } catch (\Throwable $e) {
            return false;
        }
    }
}
