<?php

declare(strict_types=1);

namespace App\Core\Authorization\ABAC;

use App\Modules\Auth\Domain\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * AttributeBasedAccessControl (ABAC)
 *
 * Evaluates access policies based on user attributes, resource attributes,
 * and contextual conditions rather than role-only checks.
 *
 * Policies are expressed as callables: fn(User $user, array $resource, array $env): bool
 *
 * Usage:
 *   $abac = app(Abac::class);
 *   $abac->allows($user, 'inventory.read', $productAttributes, $contextAttributes);
 */
class Abac
{
    /** @var array<string, array<callable>> */
    private array $policies = [];

    /**
     * Register an ABAC policy for an action.
     *
     * @param  string   $action   e.g. "inventory.delete"
     * @param  callable $policy   fn(User $user, array $resource, array $env): bool
     */
    public function define(string $action, callable $policy): void
    {
        $this->policies[$action][] = $policy;
    }

    /**
     * Check whether a user is allowed to perform an action.
     *
     * @param  User              $user      The subject
     * @param  string            $action    e.g. "order.create"
     * @param  array<string,mixed> $resource  Resource attributes
     * @param  array<string,mixed> $env       Environmental context (IP, time, etc.)
     * @return bool
     */
    public function allows(
        User $user,
        string $action,
        array $resource = [],
        array $env = []
    ): bool {
        if (! isset($this->policies[$action])) {
            Log::warning("[ABAC] No policy registered for action [{$action}]. Denying.");
            return false;
        }

        foreach ($this->policies[$action] as $policy) {
            if (! $policy($user, $resource, $env)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Deny shorthand (inverse of allows).
     */
    public function denies(
        User $user,
        string $action,
        array $resource = [],
        array $env = []
    ): bool {
        return ! $this->allows($user, $action, $resource, $env);
    }
}
