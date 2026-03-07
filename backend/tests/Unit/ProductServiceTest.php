<?php

namespace Tests\Unit;

use App\Modules\Product\DTOs\ProductDTO;
use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Repositories\ProductRepositoryInterface;
use App\Modules\Product\Services\ProductService;
use Illuminate\Support\Facades\Event;
use Mockery;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    private ProductService $service;
    private $repositoryMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repositoryMock = Mockery::mock(ProductRepositoryInterface::class);
        $this->service        = new ProductService($this->repositoryMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_create_product_dispatches_event(): void
    {
        Event::fake();

        $product = new Product();
        $product->forceFill([
            'id'        => 'product-uuid',
            'tenant_id' => 'tenant-uuid',
            'sku'       => 'TEST-001',
            'name'      => 'Test Product',
            'price'     => 10.00,
        ]);

        $this->repositoryMock
            ->shouldReceive('findBySku')
            ->with('TEST-001', 'tenant-uuid')
            ->once()
            ->andReturn(null);

        $this->repositoryMock
            ->shouldReceive('create')
            ->once()
            ->andReturn($product);

        $dto = new ProductDTO(
            tenantId: 'tenant-uuid',
            sku:      'TEST-001',
            name:     'Test Product',
            price:    10.00,
        );

        $result = $this->service->create($dto);

        Event::assertDispatched(ProductCreated::class);
        $this->assertEquals('TEST-001', $result->sku);
    }

    public function test_create_product_throws_on_duplicate_sku(): void
    {
        $existingProduct = new Product();
        $existingProduct->forceFill(['sku' => 'DUPLICATE-SKU']);

        $this->repositoryMock
            ->shouldReceive('findBySku')
            ->with('DUPLICATE-SKU', 'tenant-uuid')
            ->once()
            ->andReturn($existingProduct);

        $dto = new ProductDTO(
            tenantId: 'tenant-uuid',
            sku:      'DUPLICATE-SKU',
            name:     'Another Product',
        );

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Product SKU already exists: DUPLICATE-SKU');

        $this->service->create($dto);
    }

    public function test_find_product_throws_when_not_found(): void
    {
        $this->repositoryMock
            ->shouldReceive('findById')
            ->with('non-existent', 'tenant-uuid')
            ->once()
            ->andReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->service->findById('non-existent', 'tenant-uuid');
    }
}
