<?php

declare(strict_types=1);

namespace App\Shared\Auth;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * ABAC Policy Evaluator.
 *
 * Evaluates Attribute-Based Access Control policies by comparing subject,
 * resource, action, and environment attributes against a set of policy rules.
 *
 * Policies are loaded from config/abac.php and/or the `abac_policies` DB table.
 *
 * Policy rule structure (stored as JSON):
 * {
 *   "combiner": "AND" | "OR",
 *   "rules": [
 *     { "attribute": "subject.roles",  "operator": "contains",  "value": "admin" },
 *     { "attribute": "action.method",  "operator": "in",        "value": ["GET","HEAD"] },
 *     { "attribute": "environment.is_secure", "operator": "equals", "value": true }
 *   ]
 * }
 *
 * Supported operators:
 *   equals | not_equals | contains | not_contains | in | not_in
 *   starts_with | ends_with | greater_than | less_than | regex
 */
final class AbacPolicyEvaluator
{
    private const string POLICIES_TABLE = 'abac_policies';
    private const int CACHE_TTL = 300;

    public function __construct(
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {}

    // ─────────────────────────────────────────────────────────────────────────
    // Public API
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Evaluate whether the subject is allowed to perform the action on the
     * resource in the given environment context.
     *
     * @param  array<string,mixed>  $subject      Authenticated user attributes.
     * @param  array<string,mixed>  $resource     Target resource attributes.
     * @param  array<string,mixed>  $action       Action being performed.
     * @param  array<string,mixed>  $environment  Contextual attributes.
     * @return bool                               True if allowed.
     */
    public function evaluate(
        array $subject,
        array $resource,
        array $action,
        array $environment,
    ): bool {
        $policyName = $resource['policy'] ?? null;

        if ($policyName === null) {
            $this->logger->debug('[ABAC] No policy specified, defaulting to deny');
            return false;
        }

        $policy = $this->loadPolicy($policyName);

        if ($policy === null) {
            $this->logger->warning('[ABAC] Policy not found, defaulting to deny', [
                'policy' => $policyName,
            ]);
            return false;
        }

        $context = [
            'subject'     => $subject,
            'resource'    => $resource,
            'action'      => $action,
            'environment' => $environment,
        ];

        return $this->evaluatePolicy($policy, $context);
    }

    /**
     * Clear the policy cache (call after policy updates).
     *
     * @return void
     */
    public function clearCache(): void
    {
        Cache::forget('abac_policies_all');
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Policy loading
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @param  string  $name
     * @return array<string,mixed>|null
     */
    private function loadPolicy(string $name): ?array
    {
        $all = $this->loadAllPolicies();

        return $all[$name] ?? null;
    }

    /**
     * Load all policies from config and DB, merged together.
     *
     * @return array<string, array<string,mixed>>
     */
    private function loadAllPolicies(): array
    {
        return Cache::remember('abac_policies_all', self::CACHE_TTL, function (): array {
            // 1. Static policies from config/abac.php
            $configPolicies = config('abac.policies', []);

            // 2. Dynamic policies from DB
            $dbPolicies = [];

            try {
                $rows = DB::table(self::POLICIES_TABLE)
                    ->where('is_active', true)
                    ->get(['name', 'definition']);

                foreach ($rows as $row) {
                    $definition = json_decode($row->definition ?? '{}', associative: true);
                    if (is_array($definition)) {
                        $dbPolicies[$row->name] = $definition;
                    }
                }
            } catch (\Throwable) {
                // DB may not be available during health checks – gracefully degrade.
            }

            return array_merge($configPolicies, $dbPolicies);
        });
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Policy evaluation
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * @param  array<string,mixed>  $policy
     * @param  array<string,mixed>  $context
     * @return bool
     */
    private function evaluatePolicy(array $policy, array $context): bool
    {
        $rules    = $policy['rules']    ?? [];
        $combiner = strtoupper($policy['combiner'] ?? 'AND');

        if (empty($rules)) {
            return false;
        }

        $results = array_map(
            fn (array $rule): bool => $this->evaluateRule($rule, $context),
            $rules,
        );

        return $combiner === 'OR'
            ? in_array(true, $results, strict: true)
            : !in_array(false, $results, strict: true);
    }

    /**
     * Evaluate a single rule against the evaluation context.
     *
     * @param  array<string,mixed>  $rule     Rule definition.
     * @param  array<string,mixed>  $context  Evaluation context.
     * @return bool
     */
    private function evaluateRule(array $rule, array $context): bool
    {
        $attributePath = $rule['attribute'] ?? '';
        $operator      = $rule['operator']  ?? 'equals';
        $expected      = $rule['value']     ?? null;

        // Handle nested rules (sub-policies).
        if (isset($rule['rules'])) {
            return $this->evaluatePolicy($rule, $context);
        }

        $actual = $this->resolveAttribute($attributePath, $context);

        return $this->applyOperator($operator, $actual, $expected);
    }

    /**
     * Resolve a dot-notation attribute path against the evaluation context.
     *
     * E.g. "subject.roles", "environment.is_secure"
     *
     * @param  string              $path
     * @param  array<string,mixed> $context
     * @return mixed
     */
    private function resolveAttribute(string $path, array $context): mixed
    {
        $parts = explode('.', $path);
        $value = $context;

        foreach ($parts as $part) {
            if (is_array($value) && array_key_exists($part, $value)) {
                $value = $value[$part];
            } else {
                return null;
            }
        }

        return $value;
    }

    /**
     * Apply an operator to the actual and expected values.
     *
     * @param  string  $operator
     * @param  mixed   $actual
     * @param  mixed   $expected
     * @return bool
     */
    private function applyOperator(string $operator, mixed $actual, mixed $expected): bool
    {
        return match ($operator) {
            'equals'       => $actual == $expected,
            'not_equals'   => $actual != $expected,
            'contains'     => is_array($actual)
                                ? in_array($expected, $actual, strict: false)
                                : str_contains((string) $actual, (string) $expected),
            'not_contains' => is_array($actual)
                                ? !in_array($expected, $actual, strict: false)
                                : !str_contains((string) $actual, (string) $expected),
            'in'           => is_array($expected) && in_array($actual, $expected, strict: false),
            'not_in'       => is_array($expected) && !in_array($actual, $expected, strict: false),
            'starts_with'  => str_starts_with((string) $actual, (string) $expected),
            'ends_with'    => str_ends_with((string) $actual, (string) $expected),
            'greater_than' => is_numeric($actual) && is_numeric($expected) && $actual > $expected,
            'less_than'    => is_numeric($actual) && is_numeric($expected) && $actual < $expected,
            'regex'        => (bool) @preg_match($expected, (string) $actual),
            default        => false,
        };
    }
}
