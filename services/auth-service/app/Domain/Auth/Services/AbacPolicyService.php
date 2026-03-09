<?php

declare(strict_types=1);

namespace App\Domain\Auth\Services;

use App\Domain\User\Entities\User;
use Illuminate\Support\Facades\Cache;

/**
 * ABAC Policy Service.
 *
 * Evaluates Attribute-Based Access Control policies.
 *
 * Policies are attribute-condition rules stored in configuration or database,
 * evaluated against the user's attributes, resource attributes, and environment.
 *
 * Example policy:
 *   {
 *     "resource": "inventory",
 *     "action": "delete",
 *     "conditions": [
 *       {"attribute": "user.role", "operator": "in", "value": ["admin", "manager"]},
 *       {"attribute": "resource.tenant_id", "operator": "eq", "attribute_ref": "user.tenant_id"}
 *     ]
 *   }
 */
class AbacPolicyService
{
    /**
     * Evaluate whether a user can perform an action on a resource.
     *
     * @param  User                 $user
     * @param  string               $action
     * @param  string               $resource
     * @param  array<string, mixed> $resourceAttributes
     * @return bool
     */
    public function evaluate(User $user, string $action, string $resource, array $resourceAttributes = []): bool
    {
        $policies = $this->getPolicies($resource, $action);

        if (empty($policies)) {
            return false;
        }

        $userAttributes = $this->buildUserAttributes($user);

        foreach ($policies as $policy) {
            if ($this->evaluatePolicy($policy, $userAttributes, $resourceAttributes)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Build a flat map of user attributes for policy evaluation.
     *
     * @param  User $user
     * @return array<string, mixed>
     */
    private function buildUserAttributes(User $user): array
    {
        return [
            'user.id'        => $user->id,
            'user.tenant_id' => $user->tenant_id,
            'user.status'    => $user->status,
            'user.roles'     => $user->roles->pluck('name')->toArray(),
            'user.email'     => $user->email,
        ];
    }

    /**
     * Retrieve ABAC policies for a given resource and action.
     *
     * @param  string $resource
     * @param  string $action
     * @return array<int, array<string, mixed>>
     */
    private function getPolicies(string $resource, string $action): array
    {
        $cacheKey = "abac:policies:{$resource}:{$action}";

        return Cache::remember($cacheKey, 300, function () use ($resource, $action): array {
            return \App\Domain\Auth\Entities\AbacPolicy::where('resource', $resource)
                ->where('action', $action)
                ->where('is_active', true)
                ->get()
                ->map(fn ($p) => $p->conditions)
                ->toArray();
        });
    }

    /**
     * Evaluate a single policy's conditions.
     *
     * @param  array<string, mixed> $policy
     * @param  array<string, mixed> $userAttributes
     * @param  array<string, mixed> $resourceAttributes
     * @return bool
     */
    private function evaluatePolicy(
        array $policy,
        array $userAttributes,
        array $resourceAttributes,
    ): bool {
        $conditions = $policy['conditions'] ?? [];

        foreach ($conditions as $condition) {
            if (!$this->evaluateCondition($condition, $userAttributes, $resourceAttributes)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Evaluate a single condition.
     *
     * @param  array<string, mixed> $condition
     * @param  array<string, mixed> $userAttributes
     * @param  array<string, mixed> $resourceAttributes
     * @return bool
     */
    private function evaluateCondition(
        array $condition,
        array $userAttributes,
        array $resourceAttributes,
    ): bool {
        $attribute = $condition['attribute'];
        $operator  = $condition['operator'];

        // Resolve the left-hand value
        $leftValue = $userAttributes[$attribute]
            ?? $resourceAttributes[str_replace('resource.', '', $attribute)]
            ?? null;

        // Resolve the right-hand value (literal or attribute reference)
        $rightValue = isset($condition['attribute_ref'])
            ? ($userAttributes[$condition['attribute_ref']]
                ?? $resourceAttributes[str_replace('resource.', '', $condition['attribute_ref'])]
                ?? null)
            : ($condition['value'] ?? null);

        return match ($operator) {
            'eq'          => $leftValue === $rightValue,
            'neq'         => $leftValue !== $rightValue,
            'in'          => is_array($rightValue) && in_array($leftValue, $rightValue, true),
            'not_in'      => is_array($rightValue) && !in_array($leftValue, $rightValue, true),
            'contains'    => is_array($leftValue) && in_array($rightValue, $leftValue, true),
            'gt'          => is_numeric($leftValue) && is_numeric($rightValue) && $leftValue > $rightValue,
            'gte'         => is_numeric($leftValue) && is_numeric($rightValue) && $leftValue >= $rightValue,
            'lt'          => is_numeric($leftValue) && is_numeric($rightValue) && $leftValue < $rightValue,
            'lte'         => is_numeric($leftValue) && is_numeric($rightValue) && $leftValue <= $rightValue,
            'not_null'    => $leftValue !== null,
            'is_null'     => $leftValue === null,
            default       => false,
        };
    }
}
