<?php

namespace Tests\Unit\Product;

use App\Modules\Product\DTOs\ProductDTO;
use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Repositories\Interfaces\ProductRepositoryInterface;
use App\Modules\Product\Services\ProductService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\DB;
use Mockery;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    private ProductService $service;
    private ProductRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = Mockery::mock(ProductRepositoryInterface::class);
        $this->service    = new ProductService($this->repository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function it_creates_a_product_successfully(): void
    {
        Event::fake();

        $dto = new ProductDTO(
            name:        'Test Product',
            sku:         'TEST-001',
            description: 'A test product',
            price:       99.99,
            category:    'Electronics',
            status:      'active',
        );

        $product        = new Product();
        $product->id    = 1;
        $product->name  = $dto->name;
        $product->sku   = $dto->sku;
        $product->price = $dto->price;

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($cb) => $cb());

        $this->repository
            ->shouldReceive('findBySku')
            ->with($dto->sku)
            ->once()
            ->andReturn(null);

        $this->repository
            ->shouldReceive('create')
            ->with($dto)
            ->once()
            ->andReturn($product);

        $result = $this->service->createProduct($dto);

        $this->assertInstanceOf(Product::class, $result);
        $this->assertEquals('TEST-001', $result->sku);
        Event::assertDispatched(ProductCreated::class);
    }

    /** @test */
    public function it_throws_exception_when_sku_already_exists(): void
    {
        $dto = new ProductDTO(
            name:        'Test Product',
            sku:         'EXISTING-001',
            description: 'A test product',
            price:       99.99,
            category:    'Electronics',
        );

        $existingProduct      = new Product();
        $existingProduct->id  = 99;
        $existingProduct->sku = 'EXISTING-001';

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($cb) => $cb());

        $this->repository
            ->shouldReceive('findBySku')
            ->with($dto->sku)
            ->once()
            ->andReturn($existingProduct);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("A product with SKU 'EXISTING-001' already exists.");

        $this->service->createProduct($dto);
    }

    /** @test */
    public function it_throws_model_not_found_exception_when_product_not_found(): void
    {
        $this->repository
            ->shouldReceive('findById')
            ->with(999)
            ->once()
            ->andReturn(null);

        $this->expectException(ModelNotFoundException::class);

        $this->service->getProduct(999);
    }

    /** @test */
    public function it_deletes_a_product_and_fires_event(): void
    {
        Event::fake();

        $product       = new Product();
        $product->id   = 1;
        $product->sku  = 'TEST-001';
        $product->name = 'Test Product';

        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($cb) => $cb());

        $this->repository->shouldReceive('findById')->with(1)->once()->andReturn($product);
        $this->repository->shouldReceive('delete')->with(1)->once()->andReturn(true);

        $result = $this->service->deleteProduct(1);

        $this->assertTrue($result);
        Event::assertDispatched(\App\Modules\Product\Events\ProductDeleted::class);
    }
}
