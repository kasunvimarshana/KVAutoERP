<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Modules\Auth\Domain\Models\User;
use App\Modules\Tenant\Domain\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for the Health Check API endpoints.
 */
class HealthCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_check_returns_200_with_healthy_status(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'timestamp',
                'version',
                'checks' => [
                    'database',
                    'cache',
                    'message_broker',
                ],
            ]);
    }

    public function test_liveness_probe_returns_200(): void
    {
        $response = $this->getJson('/api/v1/health/live');

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'alive']);
    }

    public function test_readiness_probe_returns_200(): void
    {
        $response = $this->getJson('/api/v1/health/ready');

        $response->assertStatus(200)
            ->assertJsonFragment(['status' => 'ready']);
    }
}
