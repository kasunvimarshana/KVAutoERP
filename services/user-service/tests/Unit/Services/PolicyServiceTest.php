<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\PolicyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PolicyServiceTest extends TestCase
{
    use RefreshDatabase;

    private PolicyService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new PolicyService();
    }

    // ──────────────────────────────────────────────────────────
    // Action matching
    // ──────────────────────────────────────────────────────────

    public function test_wildcard_action_matches_any_action(): void
    {
        // With no policies in DB, defaults to deny — test match logic via mock
        $this->assertTrue($this->invokeMatchesAction('*', 'users:read'));
        $this->assertTrue($this->invokeMatchesAction('*', 'products:delete'));
    }

    public function test_glob_action_matches_correctly(): void
    {
        $this->assertTrue($this->invokeMatchesAction('users:*', 'users:read'));
        $this->assertTrue($this->invokeMatchesAction('users:*', 'users:create'));
        $this->assertFalse($this->invokeMatchesAction('users:*', 'products:read'));
        $this->assertFalse($this->invokeMatchesAction('users:read', 'users:create'));
    }

    public function test_exact_action_requires_exact_match(): void
    {
        $this->assertTrue($this->invokeMatchesAction('users:read', 'users:read'));
        $this->assertFalse($this->invokeMatchesAction('users:read', 'users:write'));
    }

    // ──────────────────────────────────────────────────────────
    // Condition matching
    // ──────────────────────────────────────────────────────────

    public function test_wildcard_condition_matches_any_value(): void
    {
        $matched = $this->invokeMatchesConditions(['role' => '*'], ['role' => 'admin']);

        $this->assertTrue($matched);
    }

    public function test_array_condition_requires_intersection(): void
    {
        $conditions = ['roles' => ['admin', 'manager']];
        $attributes = ['roles' => ['admin', 'viewer']];

        $this->assertTrue($this->invokeMatchesConditions($conditions, $attributes));

        $attributes2 = ['roles' => ['viewer']];
        $this->assertFalse($this->invokeMatchesConditions($conditions, $attributes2));
    }

    public function test_string_condition_requires_exact_match(): void
    {
        $this->assertTrue($this->invokeMatchesConditions(['tenant_id' => 'tenant-1'], ['tenant_id' => 'tenant-1']));
        $this->assertFalse($this->invokeMatchesConditions(['tenant_id' => 'tenant-1'], ['tenant_id' => 'tenant-2']));
    }

    public function test_placeholder_condition_resolves_from_attributes(): void
    {
        $conditions = ['tenant_id' => '{{tenant_id}}'];
        $attributes = ['tenant_id' => 'resolved-tenant'];

        $this->assertTrue($this->invokeMatchesConditions($conditions, $attributes));
    }

    public function test_missing_condition_key_fails(): void
    {
        $conditions = ['branch_id' => 'branch-1'];
        $attributes = ['tenant_id' => 'tenant-1']; // no branch_id

        $this->assertFalse($this->invokeMatchesConditions($conditions, $attributes));
    }

    // ──────────────────────────────────────────────────────────
    // Integration: evaluate() returns false by default (fail-secure)
    // ──────────────────────────────────────────────────────────

    public function test_evaluate_returns_false_when_no_policies(): void
    {
        // No policies in DB → fail-secure deny
        $result = $this->service->evaluate(
            subject:  ['sub' => 'u-1', 'tenant_id' => 'tenant-1', 'roles' => ['viewer']],
            action:   'documents:delete',
        );

        $this->assertFalse($result);
    }

    // ──────────────────────────────────────────────────────────
    // Helpers — access private methods via reflection
    // ──────────────────────────────────────────────────────────

    private function invokeMatchesAction(string $policyAction, string $requestedAction): bool
    {
        $method = new \ReflectionMethod(PolicyService::class, 'matchesAction');
        $method->setAccessible(true);

        return (bool) $method->invoke($this->service, $policyAction, $requestedAction);
    }

    private function invokeMatchesConditions(array $conditions, array $attributes): bool
    {
        $method = new \ReflectionMethod(PolicyService::class, 'matchesConditions');
        $method->setAccessible(true);

        return (bool) $method->invoke($this->service, $conditions, $attributes);
    }
}
