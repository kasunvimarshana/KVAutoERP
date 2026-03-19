<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Verify the user-service health endpoint is reachable and returns the
     * standard success envelope. This acts as a smoke test for the service.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonPath('data.service', 'user-service');
    }
}
