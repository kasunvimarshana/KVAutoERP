<?php

namespace Tests\Feature;

use App\Modules\User\Models\User;
use App\Modules\User\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class UserApiTest extends TestCase
{
    use RefreshDatabase;

    private string $tenantId = '550e8400-e29b-41d4-a716-446655440000';

    protected function setUp(): void
    {
        parent::setUp();

        // Bind tenant_id in the container as middleware would
        app()->instance('tenant_id', $this->tenantId);
    }

    protected function apiHeaders(): array
    {
        return [
            'Accept'       => 'application/json',
            'Content-Type' => 'application/json',
            'X-Tenant-ID'  => $this->tenantId,
        ];
    }

    public function test_health_endpoint_returns_ok(): void
    {
        $response = $this->getJson('/api/health');
        $response->assertStatus(200)
                 ->assertJsonPath('status', 'ok');
    }

    public function test_user_index_requires_authentication(): void
    {
        $response = $this->getJson('/api/v1/users', ['Accept' => 'application/json']);
        // Without auth.keycloak bypass, expect 401
        $response->assertStatus(401);
    }

    public function test_create_user_validates_required_fields(): void
    {
        // Mock the Keycloak middleware by bypassing it
        $this->withoutMiddleware(\App\Http\Middleware\AuthenticateWithKeycloak::class)
             ->withoutMiddleware(\App\Http\Middleware\TenantMiddleware::class);

        app()->instance('tenant_id', $this->tenantId);

        $userRepo = Mockery::mock(UserRepositoryInterface::class);
        $userRepo->shouldReceive('findById')->andReturn(null)->byDefault();
        $userRepo->shouldReceive('paginate')->andReturn(
            Mockery::mock(LengthAwarePaginator::class, [
                'items'        => [],
                'total'        => 0,
                'perPage'      => 15,
                'currentPage'  => 1,
                'lastPage'     => 1,
                'toArray'      => ['data' => [], 'meta' => []],
            ])
        )->byDefault();

        app()->instance(UserRepositoryInterface::class, $userRepo);

        $response = $this->postJson('/api/v1/users', [], $this->apiHeaders());

        $response->assertStatus(422)
                 ->assertJsonStructure(['errors']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
