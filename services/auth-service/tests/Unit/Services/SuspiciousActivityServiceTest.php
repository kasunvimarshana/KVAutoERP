<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\SuspiciousActivityService;
use Illuminate\Support\Facades\Redis;
use Tests\TestCase;

class SuspiciousActivityServiceTest extends TestCase
{
    private SuspiciousActivityService $service;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'auth.activity.max_attempts' => 3,
            'auth.activity.lockout_ttl'  => 900,
            'auth.activity.attempt_ttl'  => 300,
        ]);

        $this->service = new SuspiciousActivityService();
    }

    public function test_is_not_blocked_by_default(): void
    {
        Redis::shouldReceive('exists')->andReturn(0);

        $this->assertFalse($this->service->isBlocked('test@example.com'));
    }

    public function test_remaining_attempts_decrements_on_failure(): void
    {
        Redis::shouldReceive('incr')->andReturn(1);
        Redis::shouldReceive('expire')->andReturn(true);
        Redis::shouldReceive('exists')->andReturn(0);
        Redis::shouldReceive('get')->andReturn('1');

        $this->service->recordFailedAttempt('user@example.com', '127.0.0.1');

        Redis::shouldReceive('get')->andReturn('1');

        $remaining = $this->service->remainingAttempts('user@example.com');
        $this->assertSame(2, $remaining); // max 3 - 1 attempt
    }

    public function test_blocks_after_max_attempts(): void
    {
        // Simulate 3 failed attempts exceeding threshold
        Redis::shouldReceive('incr')->andReturn(3, 3);    // email count = 3, IP count = 3
        Redis::shouldReceive('expire')->andReturn(true);
        Redis::shouldReceive('exists')->andReturn(0, 0);  // not yet blocked before setex
        Redis::shouldReceive('setex')->andReturn(true);

        $blocked = $this->service->recordFailedAttempt('user@example.com', '127.0.0.1');

        $this->assertTrue($blocked);
    }

    public function test_reset_deletes_attempt_counter(): void
    {
        Redis::shouldReceive('del')->once();

        $this->service->resetFailedAttempts('user@example.com');

        // No assertions needed — mock verifies del() was called
        $this->addToAssertionCount(1);
    }

    public function test_block_sets_redis_key(): void
    {
        Redis::shouldReceive('setex')->once();

        $this->service->block('user@example.com', 600);

        $this->addToAssertionCount(1);
    }

    public function test_unblock_removes_redis_keys(): void
    {
        Redis::shouldReceive('del')->twice();

        $this->service->unblock('user@example.com');

        $this->addToAssertionCount(1);
    }
}
