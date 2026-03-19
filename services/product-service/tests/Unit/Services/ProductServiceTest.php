<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use KvEnterprise\SharedKernel\Exceptions\NotFoundException;
use KvEnterprise\SharedKernel\Exceptions\ValidationException;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * Unit tests for ProductService.
 *
 * Uses Mockery to mock the ProductRepositoryInterface so that the service
 * logic can be tested in complete isolation from the database.
 * Extends Tests\TestCase (Laravel app) to allow Facade mocking via
 * DB::shouldReceive() for transaction wrapping.
 */
final class ProductServiceTest extends TestCase
{
    private ProductRepositoryInterface&MockInterface $repository;
    private ProductService $service;

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

    // -------------------------------------------------------------------------
    // findOrFail
    // -------------------------------------------------------------------------

    /** @test */
    public function it_returns_a_product_when_found_by_id(): void
    {
        $product = $this->makeProduct(['id' => 'prod-001', 'sku' => 'SKU-001']);

        $this->repository
            ->shouldReceive('findById')
            ->once()
            ->with('prod-001')
            ->andReturn($product);

        $result = $this->service->findOrFail('prod-001');

        self::assertSame($product->id, $result->id);
        self::assertSame($product->sku, $result->sku);
    }

    /** @test */
    public function it_throws_not_found_exception_when_product_does_not_exist(): void
    {
        $this->repository
            ->shouldReceive('findById')
            ->once()
            ->with('missing-id')
            ->andReturn(null);

        $this->expectException(NotFoundException::class);

        $this->service->findOrFail('missing-id');
    }

    // -------------------------------------------------------------------------
    // list
    // -------------------------------------------------------------------------

    /** @test */
    public function it_returns_a_paginator_with_default_page_size(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 15, 1);

        $this->repository
            ->shouldReceive('paginate')
            ->once()
            ->andReturn($paginator);

        $result = $this->service->list();

        self::assertSame($paginator, $result);
    }

    /** @test */
    public function it_passes_filters_to_the_paginator(): void
    {
        $paginator = new LengthAwarePaginator([], 0, 15, 1);

        $this->repository
            ->shouldReceive('paginate')
            ->once()
            ->with(2, 25, Mockery::type(\KvEnterprise\SharedKernel\DTOs\FilterDTO::class))
            ->andReturn($paginator);

        $result = $this->service->list(
            filters: ['type' => 'physical', 'status' => 'active'],
            page: 2,
            perPage: 25,
        );

        self::assertSame($paginator, $result);
    }

    // -------------------------------------------------------------------------
    // create
    // -------------------------------------------------------------------------

    /** @test */
    public function it_creates_a_product_with_generated_slug(): void
    {
        $data = [
            'sku'             => 'SKU-NEW',
            'name'            => 'My New Product',
            'type'            => 'physical',
            'organization_id' => 'org-001',
        ];

        $this->repository
            ->shouldReceive('findBySku')
            ->once()
            ->with('SKU-NEW')
            ->andReturn(null);

        $created = $this->makeProduct(array_merge($data, ['slug' => 'my-new-product']));

        $this->repository
            ->shouldReceive('create')
            ->once()
            ->andReturn($created);

        // Wrap DB::transaction to execute the callback synchronously.
        DB::shouldReceive('transaction')->once()->andReturnUsing(
            static fn ($callback) => $callback(),
        );

        $result = $this->service->create($data);

        self::assertSame('my-new-product', $result->slug);
    }

    /** @test */
    public function it_throws_validation_exception_when_sku_is_already_in_use(): void
    {
        $existing = $this->makeProduct(['sku' => 'DUPLICATE-SKU']);

        $this->repository
            ->shouldReceive('findBySku')
            ->once()
            ->with('DUPLICATE-SKU')
            ->andReturn($existing);

        $this->expectException(ValidationException::class);

        $this->service->create([
            'sku'             => 'DUPLICATE-SKU',
            'name'            => 'Another Product',
            'type'            => 'physical',
            'organization_id' => 'org-001',
        ]);
    }

    // -------------------------------------------------------------------------
    // update
    // -------------------------------------------------------------------------

    /** @test */
    public function it_updates_a_product_and_regenerates_slug_when_name_changes(): void
    {
        $product = $this->makeProduct([
            'id'   => 'prod-001',
            'sku'  => 'OLD-SKU',
            'name' => 'Old Name',
            'slug' => 'old-name',
        ]);

        $updated = $this->makeProduct([
            'id'   => 'prod-001',
            'sku'  => 'OLD-SKU',
            'name' => 'New Name',
            'slug' => 'new-name',
        ]);

        $this->repository
            ->shouldReceive('findById')
            ->once()
            ->with('prod-001')
            ->andReturn($product);

        $this->repository
            ->shouldReceive('update')
            ->once()
            ->andReturn($updated);

        DB::shouldReceive('transaction')->once()->andReturnUsing(
            static fn ($callback) => $callback(),
        );

        $result = $this->service->update('prod-001', ['name' => 'New Name']);

        self::assertSame('new-name', $result->slug);
    }

    /** @test */
    public function it_throws_validation_exception_when_updated_sku_is_taken(): void
    {
        $product  = $this->makeProduct(['id' => 'prod-001', 'sku' => 'OLD-SKU']);
        $conflict = $this->makeProduct(['id' => 'prod-002', 'sku' => 'TAKEN-SKU']);

        $this->repository
            ->shouldReceive('findById')
            ->once()
            ->with('prod-001')
            ->andReturn($product);

        $this->repository
            ->shouldReceive('findBySku')
            ->once()
            ->with('TAKEN-SKU')
            ->andReturn($conflict);

        $this->expectException(ValidationException::class);

        $this->service->update('prod-001', ['sku' => 'TAKEN-SKU']);
    }

    // -------------------------------------------------------------------------
    // delete
    // -------------------------------------------------------------------------

    /** @test */
    public function it_soft_deletes_a_product(): void
    {
        $product = $this->makeProduct(['id' => 'prod-001']);

        $this->repository
            ->shouldReceive('findById')
            ->once()
            ->with('prod-001')
            ->andReturn($product);

        $this->repository
            ->shouldReceive('delete')
            ->once()
            ->with($product);

        $this->service->delete('prod-001');
        self::assertTrue(true);
    }

    /** @test */
    public function it_throws_not_found_exception_when_deleting_missing_product(): void
    {
        $this->repository
            ->shouldReceive('findById')
            ->once()
            ->with('missing')
            ->andReturn(null);

        $this->expectException(NotFoundException::class);

        $this->service->delete('missing');
    }

    // -------------------------------------------------------------------------
    // addPrice
    // -------------------------------------------------------------------------

    /** @test */
    public function it_throws_not_found_when_adding_price_to_missing_product(): void
    {
        $this->repository
            ->shouldReceive('findById')
            ->once()
            ->andReturn(null);

        $this->expectException(NotFoundException::class);

        $this->service->addPrice('no-such-product', [
            'currency_code' => 'USD',
            'price_type'    => 'base',
            'price'         => '10.00',
        ]);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Build a Product model with attributes set directly — no DB interaction.
     *
     * @param  array<string, mixed>  $attributes
     * @return Product
     */
    private function makeProduct(array $attributes): Product
    {
        $product = new Product();

        foreach ($attributes as $key => $value) {
            $product->setAttribute($key, $value);
        }

        return $product;
    }
}
