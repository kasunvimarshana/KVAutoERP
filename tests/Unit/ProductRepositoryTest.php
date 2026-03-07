<?php

namespace Tests\Unit;

use App\Modules\Product\Models\Product;
use App\Modules\Product\Repositories\ProductRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private ProductRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new ProductRepository(new Product());
    }

    public function test_create_persists_product(): void
    {
        $data = [
            'name'  => 'Repo Test',
            'price' => 5.00,
            'stock' => 10,
            'sku'   => 'SKU-REPO-001',
        ];

        $product = $this->repository->create($data);

        $this->assertInstanceOf(Product::class, $product);
        $this->assertDatabaseHas('products', ['sku' => 'SKU-REPO-001']);
    }

    public function test_find_returns_product(): void
    {
        $product = Product::factory()->create();

        $found = $this->repository->find($product->id);

        $this->assertNotNull($found);
        $this->assertEquals($product->id, $found->id);
    }

    public function test_find_returns_null_for_missing_id(): void
    {
        $this->assertNull($this->repository->find(9999));
    }

    public function test_update_changes_product_data(): void
    {
        $product = Product::factory()->create(['name' => 'Before']);

        $updated = $this->repository->update($product->id, ['name' => 'After']);

        $this->assertEquals('After', $updated->name);
    }

    public function test_delete_soft_deletes_product(): void
    {
        $product = Product::factory()->create();

        $result = $this->repository->delete($product->id);

        $this->assertTrue($result);
        $this->assertSoftDeleted('products', ['id' => $product->id]);
    }
}
