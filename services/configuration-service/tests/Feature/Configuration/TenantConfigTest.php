<?php

declare(strict_types=1);

namespace Tests\Feature\Configuration;

use App\Models\FeatureFlag;
use App\Models\TenantConfiguration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantConfigTest extends TestCase
{
    use RefreshDatabase;

    private string $tenantId = 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa';

    private function authHeaders(): array
    {
        // Build a minimal JWT with required claims (base64url encoded)
        $header  = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'sub'       => 'user-001',
            'user_id'   => 'user-001',
            'tenant_id' => $this->tenantId,
            'exp'       => time() + 3600,
        ]));
        $token = "{$header}.{$payload}.fake-signature";

        return ['Authorization' => "Bearer {$token}"];
    }

    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/v1/health');

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'ok');
        $response->assertJsonPath('service', 'configuration-service');
    }

    public function test_index_returns_paginated_configurations(): void
    {
        TenantConfiguration::factory()->count(3)->forTenant($this->tenantId)->create();

        $response = $this->getJson(
            '/api/v1/config?tenant_id=' . $this->tenantId,
            $this->authHeaders(),
        );

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure(['success', 'data', 'message', 'meta']);
        $this->assertCount(3, $response->json('data'));
    }

    public function test_store_creates_configuration(): void
    {
        $payload = [
            'tenant_id'    => $this->tenantId,
            'service_name' => 'orders',
            'config_key'   => 'order.currency',
            'config_value' => ['value' => 'USD'],
            'config_type'  => 'string',
        ];

        $response = $this->postJson('/api/v1/config', $payload, $this->authHeaders());

        $response->assertStatus(201);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.config_key', 'order.currency');
        $this->assertDatabaseHas('tenant_configurations', [
            'tenant_id'    => $this->tenantId,
            'service_name' => 'orders',
            'config_key'   => 'order.currency',
        ]);
    }

    public function test_store_returns_422_with_invalid_payload(): void
    {
        $response = $this->postJson('/api/v1/config', [], $this->authHeaders());

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['tenant_id', 'service_name', 'config_key', 'config_value', 'config_type']);
    }

    public function test_store_returns_409_when_key_already_exists(): void
    {
        TenantConfiguration::factory()->forTenant($this->tenantId)->create([
            'service_name' => 'orders',
            'config_key'   => 'order.currency',
        ]);

        $response = $this->postJson('/api/v1/config', [
            'tenant_id'    => $this->tenantId,
            'service_name' => 'orders',
            'config_key'   => 'order.currency',
            'config_value' => ['value' => 'EUR'],
            'config_type'  => 'string',
        ], $this->authHeaders());

        $response->assertStatus(409);
        $response->assertJsonPath('success', false);
    }

    public function test_show_returns_configuration(): void
    {
        $config = TenantConfiguration::factory()->forTenant($this->tenantId)->create();

        $response = $this->getJson("/api/v1/config/{$config->id}", $this->authHeaders());

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.id', $config->id);
    }

    public function test_show_returns_404_for_nonexistent_config(): void
    {
        $response = $this->getJson('/api/v1/config/nonexistent-id', $this->authHeaders());

        $response->assertStatus(404);
        $response->assertJsonPath('success', false);
    }

    public function test_destroy_soft_deletes_configuration(): void
    {
        $config = TenantConfiguration::factory()->forTenant($this->tenantId)->create();

        $response = $this->deleteJson("/api/v1/config/{$config->id}", [], $this->authHeaders());

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertSoftDeleted('tenant_configurations', ['id' => $config->id]);
    }

    public function test_unauthenticated_request_returns_401(): void
    {
        $response = $this->getJson('/api/v1/config');

        $response->assertStatus(401);
        $response->assertJsonPath('success', false);
    }

    public function test_feature_flag_toggle_changes_state(): void
    {
        $flag = FeatureFlag::factory()->forTenant($this->tenantId)->enabled()->create();

        $response = $this->postJson("/api/v1/features/{$flag->id}/toggle", [], $this->authHeaders());

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.is_enabled', false);
    }

    public function test_feature_flag_check_returns_status(): void
    {
        FeatureFlag::factory()->forTenant($this->tenantId)->create([
            'flag_key'   => 'feature.test.enabled',
            'is_enabled' => true,
        ]);

        $response = $this->getJson(
            '/api/v1/features/check/feature.test.enabled?tenant_id=' . $this->tenantId,
            $this->authHeaders(),
        );

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.is_enabled', true);
    }

    public function test_get_service_config_returns_key_value_map(): void
    {
        TenantConfiguration::factory()->forTenant($this->tenantId)->create([
            'service_name' => 'inventory',
            'config_key'   => 'stock.uom',
            'config_value' => ['value' => 'each'],
            'config_type'  => 'string',
            'is_active'    => true,
        ]);

        $response = $this->getJson(
            "/api/v1/config/{$this->tenantId}/inventory",
            $this->authHeaders(),
        );

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        $this->assertArrayHasKey('stock.uom', $response->json('data'));
    }
}
