<?php

namespace App\Modules\Product\Tests\Unit;

use App\Modules\Product\Events\ProductCreated;
use App\Modules\Product\Events\ProductDeleted;
use App\Modules\Product\Events\ProductUpdated;
use App\Modules\Product\Models\Product;
use App\Modules\Product\Repositories\Contracts\ProductRepositoryInterface;
use App\Modules\Product\Services\ProductService;
use App\Saga\SagaOrchestrator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    private ProductService $service;
    private ProductRepositoryInterface $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->app->make(ProductRepositoryInterface::class);
        $this->service    = new ProductService($this->repository, new SagaOrchestrator());
    }

    public function test_create_fires_product_created_event(): void
    {
        Event::fake();

        $this->service->create([
            'name'  => 'Event Test',
            'price' => 1.00,
            'stock' => 1,
            'sku'   => 'SKU-EVT-001',
        ]);

        Event::assertDispatched(ProductCreated::class);
    }

    public function test_update_fires_product_updated_event(): void
    {
        $product = Product::factory()->create();

        Event::fake();
        $this->service->update($product->id, ['name' => 'Updated']);

        Event::assertDispatched(ProductUpdated::class);
    }

    public function test_delete_fires_product_deleted_event(): void
    {
        $product = Product::factory()->create();

        Event::fake();
        $this->service->delete($product->id);

        Event::assertDispatched(ProductDeleted::class);
    }
}
