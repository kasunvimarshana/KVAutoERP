<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Passport\ClientRepository;

abstract class TestCase extends BaseTestCase
{
    /**
     * Set up Passport personal access client for each test.
     *
     * The client is required by Passport to issue personal access tokens
     * (used in test assertions that call actingAs).
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create a Passport personal access client so token issuance works
        if (class_exists(\Laravel\Passport\Passport::class)) {
            $this->ensurePassportPersonalAccessClient();
        }
    }

    /**
     * Create a Passport personal access client if one doesn't already exist.
     */
    private function ensurePassportPersonalAccessClient(): void
    {
        try {
            $clientRepo = app(ClientRepository::class);
            $clientRepo->createPersonalAccessGrantClient(
                'Test Personal Access Client',
                null  // null = default provider
            );
        } catch (\Throwable $e) {
            // Client might already exist; ignore
        }
    }
}
