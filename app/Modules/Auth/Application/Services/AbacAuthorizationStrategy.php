<?php

namespace Modules\Auth\Application\Services;

use Illuminate\Support\Facades\Gate;
use Modules\Auth\Application\Contracts\AuthorizationStrategyInterface;
use Modules\User\Infrastructure\Persistence\Eloquent\Models\UserModel;

/**
 * Attribute-Based Access Control (ABAC) authorization strategy.
 * Delegates to Laravel's Gate / Policy system for fine-grained, attribute-aware checks.
 */
class AbacAuthorizationStrategy implements AuthorizationStrategyInterface
{
    public function getName(): string
    {
        return 'abac';
    }

    public function authorize(int $userId, string $ability, mixed $subject = null): bool
    {
        $user = UserModel::find($userId);

        if (!$user) {
            return false;
        }

        return $subject !== null
            ? Gate::forUser($user)->allows($ability, $subject)
            : Gate::forUser($user)->allows($ability);
    }
}
