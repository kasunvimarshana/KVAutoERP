<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\Repositories\TenantConfigurationRepositoryInterface;
use App\DTOs\TenantConfigurationDto;
use App\Exceptions\ConfigurationException;
use App\Models\TenantConfiguration;
use App\Services\TenantConfigService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class TenantConfigServiceTest extends TestCase
{
    private MockInterface $repository;
    private TenantConfigService $service;
    private string $tenantId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = Mockery::mock(TenantConfigurationRepositoryInterface::class);
        $this->service = new TenantConfigService($this->repository);
        $this->tenantId = 'aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa';
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ─────────────────────────────────────────────────────────────────
    // getConfig
    // ─────────────────────────────────────────────────────────────────

    public function test_get_config_returns_typed_value_when_found(): void
    {
        $config = new TenantConfiguration([
            'tenant_id'    => $this->tenantId,
            'service_name' => 'orders',
            'config_key'   => 'order.currency',
            'config_value' => ['value' => 'USD'],
            'config_type'  => 'string',
        ]);

        Cache::shouldReceive('remember')->once()->andReturnUsing(
            fn ($key, $ttl, $callback) => $callback(),
        );

        $this->repository->shouldReceive('findByKey')
            ->with($this->tenantId, 'orders', 'order.currency')
            ->once()
            ->andReturn($config);

        $result = $this->service->getConfig($this->tenantId, 'orders', 'order.currency');

        $this->assertSame('USD', $result);
    }

    public function test_get_config_returns_null_when_not_found(): void
    {
        Cache::shouldReceive('remember')->once()->andReturnUsing(
            fn ($key, $ttl, $callback) => $callback(),
        );

        $this->repository->shouldReceive('findByKey')
            ->once()
            ->andReturn(null);

        $result = $this->service->getConfig($this->tenantId, 'orders', 'nonexistent.key');

        $this->assertNull($result);
    }

    // ─────────────────────────────────────────────────────────────────
    // create
    // ─────────────────────────────────────────────────────────────────

    public function test_create_throws_when_key_already_exists(): void
    {
        $dto = new TenantConfigurationDto(
            tenantId: $this->tenantId,
            serviceName: 'orders',
            configKey: 'order.currency',
            configValue: ['value' => 'USD'],
            configType: 'string',
        );

        $this->repository->shouldReceive('existsByKey')
            ->with($this->tenantId, 'orders', 'order.currency')
            ->once()
            ->andReturn(true);

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionCode(409);

        $this->service->create($dto);
    }

    public function test_create_returns_new_configuration_on_success(): void
    {
        $dto = new TenantConfigurationDto(
            tenantId: $this->tenantId,
            serviceName: 'inventory',
            configKey: 'stock.valuation',
            configValue: ['value' => 'fifo'],
            configType: 'string',
        );

        $created = new TenantConfiguration($dto->toArray());

        $this->repository->shouldReceive('existsByKey')->once()->andReturn(false);
        $this->repository->shouldReceive('create')->once()->andReturn($created);
        Cache::shouldReceive('forget')->once();

        $result = $this->service->create($dto);

        $this->assertInstanceOf(TenantConfiguration::class, $result);
    }

    // ─────────────────────────────────────────────────────────────────
    // delete
    // ─────────────────────────────────────────────────────────────────

    public function test_delete_throws_when_config_not_found(): void
    {
        $this->repository->shouldReceive('findById')->once()->andReturn(null);

        $this->expectException(ConfigurationException::class);
        $this->expectExceptionCode(404);

        $this->service->delete('nonexistent-id');
    }

    public function test_delete_succeeds_when_config_exists(): void
    {
        $config = new TenantConfiguration([
            'id'           => 'some-id',
            'tenant_id'    => $this->tenantId,
            'service_name' => 'orders',
            'config_key'   => 'order.currency',
            'config_value' => ['value' => 'USD'],
            'config_type'  => 'string',
        ]);

        $this->repository->shouldReceive('findById')->once()->andReturn($config);
        $this->repository->shouldReceive('delete')->once()->andReturn(true);
        Cache::shouldReceive('forget')->once();

        $this->service->delete('some-id');

        $this->addToAssertionCount(1);
    }

    // ─────────────────────────────────────────────────────────────────
    // getServiceConfig
    // ─────────────────────────────────────────────────────────────────

    public function test_get_service_config_returns_key_value_map(): void
    {
        $configs = new \Illuminate\Database\Eloquent\Collection([
            tap(new TenantConfiguration([
                'config_key'   => 'currency',
                'config_value' => ['value' => 'USD'],
                'config_type'  => 'string',
            ])),
            tap(new TenantConfiguration([
                'config_key'   => 'max_quantity',
                'config_value' => ['value' => 100],
                'config_type'  => 'integer',
            ])),
        ]);

        Cache::shouldReceive('remember')->once()->andReturnUsing(
            fn ($key, $ttl, $callback) => $callback(),
        );

        $this->repository->shouldReceive('getAllActiveForService')->once()->andReturn($configs);

        $result = $this->service->getServiceConfig($this->tenantId, 'orders');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('currency', $result);
        $this->assertSame('USD', $result['currency']);
    }

    // ─────────────────────────────────────────────────────────────────
    // listForTenant
    // ─────────────────────────────────────────────────────────────────

    public function test_list_for_tenant_returns_paginator(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 15);

        $this->repository->shouldReceive('findByTenant')
            ->with($this->tenantId, 15)
            ->once()
            ->andReturn($paginator);

        $result = $this->service->listForTenant($this->tenantId);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }
}
